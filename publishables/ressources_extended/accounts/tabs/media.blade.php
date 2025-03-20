<div class="tab-pane fade" id="nav-media" role="tabpanel"
     aria-labelledby="nav-media-tab">
    <div class="row mb-4">
        <div class="col-lg-12">
            <h4 class="mt-3">Photo de profil</h4>


        </div>
    </div>
    <input type="hidden" name="admin_processed_media" value="1"/>
</div>

@push('css')
    <style>
        .sellable-media .errors {
            display: none;
        }
        .sellable-media .media-library-uploader * {
            line-height: initial !important;
            font-size: 16px !important;
        }
        .media-library, .media-library *, .media-library-item * {
            font-size: 16px !important;
        }
    </style>
@endpush
