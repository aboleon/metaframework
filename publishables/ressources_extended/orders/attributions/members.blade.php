<h5>{{ __('front/order.members_list') }}</h5>
<div class="border-secondary-subtle border rounded-3 p-3 mt-3 shadow-sm">
@if ($groupMembers->isNotEmpty())
    <ul class="list-unstyled members-list m-0" id="members">
        @foreach($groupMembers as $member)
            <li>
                <x-mfw::checkbox name="member." :value="$member->id" :label="$member->name"/>
            </li>
        @endforeach
    </ul>
</div>
@else
    <x-mfw::alert :message="__('front/groups.has_no_members')"/>
@endif
