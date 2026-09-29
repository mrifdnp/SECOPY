<?php
namespace App\Jobs;

use App\Models\ImportStatus;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class CsvImporter implements ShouldQueue
{
    use Queueable;

    protected string $importClass;
    protected array $constructorParams;
    protected string $importStatusId;
    protected string $filePath;
    protected ?string $disk;
    protected string $readerType;
    protected string $jobName;

    /**
     * Create a new job instance.
     *
     * @param string $importClass - Fully qualified class name (e.g., App\Imports\ImportCoopMember)
     * @param array $constructorParams - Array of parameters for import class constructor
     * @param string $filePath - Path to the file to import
     * @param string|null $disk - Storage disk (optional)
     * @param string $readerType - Excel reader type (default: CSV)
     * @param string|null $jobName - Custom job name for logging (optional)
     */
    public function __construct(
        string $importClass,
        array $constructorParams,
        string $importStatusId,
        string $filePath,
        ?string $disk = null,
        string $readerType = \Maatwebsite\Excel\Excel::CSV,
        ?string $jobName = null
    ) {
        $this->importClass       = $importClass;
        $this->constructorParams = $constructorParams;
        $this->importStatusId    = $importStatusId;
        $this->filePath          = $filePath;
        $this->disk              = $disk;
        $this->readerType        = $readerType;
        $this->jobName           = $jobName ?? class_basename($importClass);
    }

    /**
     * The number of seconds the job can run before timing out.
     */
    public $timeout = 600;

    /**
     * Execute the job.
     */
    public function handle()
    {
        // Naikkan memory limit untuk proses import besar
        ini_set('memory_limit', '512M');

        try {
            // Check if import class exists
            if (! class_exists($this->importClass)) {
                throw new \Exception("Import class {$this->importClass} does not exist");
            }

            // Check if file exists
            if (! Storage::disk($this->disk)->exists($this->filePath)) {
                throw new \Exception("Import file does not exist: {$this->filePath}");
            }

            // Create instance of import class using reflection
            $reflectionClass = new \ReflectionClass($this->importClass);
            $importInstance  = $reflectionClass->newInstanceArgs($this->constructorParams);

            // Validate that the import class implements required interfaces
            $this->validateImportClass($importInstance);

            // Perform the import
            Excel::import($importInstance, $this->filePath, $this->disk, $this->readerType);
        } catch (\Exception $e) {
            Log::error("Import Job failed", [
                'job_name'     => $this->jobName,
                'import_class' => $this->importClass,
                'file_path'    => $this->filePath,
                'error'        => $e->getMessage(),
                'trace'        => $e->getTraceAsString(),
            ]);

            // Update import status to failed
            $importStatus = ImportStatus::find($this->importStatusId);
            if ($importStatus) {
                $importStatus->update([
                    'status' => 'failed',
                ]);
            }

            throw $e; // Re-throw untuk retry mechanism
        } finally {
            if (Storage::disk($this->disk)->exists($this->filePath)) {
                Storage::disk($this->disk)->delete($this->filePath);
            }
        }
    }

    /**
     * Handle a job failure.
     * Dipanggil oleh Laravel ketika job gagal total (termasuk MaxAttemptsExceededException).
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Import Job failed permanently", [
            'job_name'     => $this->jobName,
            'import_class' => $this->importClass,
            'error'        => $exception->getMessage(),
        ]);

        $importStatus = ImportStatus::find($this->importStatusId);
        if ($importStatus) {
            $importStatus->update([
                'status' => 'failed',
            ]);
        }
    }

    /**
     * Validate that import class implements required Laravel Excel interfaces
     */
    private function validateImportClass($importInstance)
    {
        $requiredInterfaces = [
            \Maatwebsite\Excel\Concerns\ToModel::class,
            \Maatwebsite\Excel\Concerns\ToCollection::class,
            \Maatwebsite\Excel\Concerns\OnEachRow::class,
        ];

        $implementsAny = false;
        foreach ($requiredInterfaces as $interface) {
            if ($importInstance instanceof $interface) {
                $implementsAny = true;
                break;
            }
        }

        if (! $implementsAny) {
            throw new \Exception(
                "Import class {$this->importClass} must implement at least one of: " .
                implode(', ', $requiredInterfaces)
            );
        }
    }
}
