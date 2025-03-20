<div class="tab-pane fade" id="documents-tabpane" role="tabpanel" aria-labelledby="documents-tabpane-tab">

    <div class="mt-4">

        <div class="row m-0">
            <div class="col-md-12 mb-4 ps-0">
                <x-media-library-collection
                        collection="documents"
                        :model="$account"
                        name="documents"
                />
            </div>
        </div>
    </div>

</div>
