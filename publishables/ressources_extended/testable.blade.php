
<x-select-domain
    name="domain_id"
    value="{{old('domain_id', $domain_id)}}"
    :event="$event"
    class="rounded-0 form-control"
    id="select_domain"/>


<x-mfw::select name="domain_id" :values="$event->domains->pluck('name','id')->sort()->toArray()" :affected="$domain_id" />
