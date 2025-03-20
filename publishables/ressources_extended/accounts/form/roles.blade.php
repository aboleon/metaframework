<x-mfw::select :values="$roles" name="roles[]" label="{{__('forms.fields.type')}}" :affected="$account?->role" :nullable="false"/>
