<div id="storeChangeForm" class="modal fade modal-primary">
    <div class="modal-dialog">
        <div class="modal-content" id="modal-filter">
            <div class="modal-header p-3 bg-primary bg-gradient">
                <h5 class="text-white"><i class="ri-filter-line"> </i> Ganti Store</h5>
                <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form_ganti_store" name="form_ganti_store" action="{{ route('store.gantiDefault') }}" method="post">
                @csrf
                <div class="modal-body">
                    <div class="box-body indoit-form form-horizontal">
                        <div class="form-group">
                            <label for="" class="col-sm-3 control-label">Store</label>
                            <div class="col-sm-12">
                                <select class="form-control changeStoreSelect" data-choices name="change_store_id" id="change_store_id" required>
                                    <option value="" selected>-- Silahkan Pilih Akses Store --</option>
                                    @forelse($storeOptionsById ?? [] as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @empty
                                        <option value="" disabled>Tidak Ada Akses Store Tersedia</option>
                                    @endforelse
                                </select>
                            </div>
                        </div>
                        <br />
                    </div>
                </div>

                <div class="modal-footer bg-opacity-75">
                    <button type="button" class="btn btn-outline btn-light pull-left" data-bs-dismiss="modal"><i class="las la-times-circle"></i> Close</button>
                    <button type="submit" class="btn btn-info pull-left text-light"><i class="ri-save-fill"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>