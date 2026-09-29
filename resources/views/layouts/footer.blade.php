<footer class="footer">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <script>document.write(new Date().getFullYear())</script> © PT. Jendela Digital Indonesia
            </div>
            <div class="col-sm-6">
                <div class="text-sm-end d-none d-sm-block">
                    IndoIT CMS v.5.0 | App Version {{ $lastVersion ? $lastVersion->version : '2.1' }} last update {{ $lastVersion ? \Carbon\Carbon::parse($lastVersion->created_date)->format('d F Y H:i:s') : '24 January 2024 18:21:19' }}
                </div>
            </div>
        </div>
    </div>
</footer>
