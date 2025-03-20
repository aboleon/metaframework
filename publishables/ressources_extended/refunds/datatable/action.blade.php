<ul class="mfw-actions">
    <x-mfw::edit-link :route="route('panel.manager.event.orders.edit', ['event' => $data->event_id, 'order' => $data->order_id])"/>
    <li data-bs-toggle="modal" data-bs-target="#send_by_mail_{{ $data->id }}">
        <a href="#" class="mfw-edit-link btn btn-sm btn-warning"
           data-bs-placement="top" data-bs-title="Envoyer par e-mail"
           data-bs-toggle="tooltip"><i class="fas fa-envelope"></i></a>
    </li>
    <li>
        <a href="{{ route('pdf-printer', ['type' => 'refundable', 'identifier' => $data->uuid]) }}" class="mfw-edit-link btn btn-sm btn-danger" target="_blank" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Avoir">
            <i class="fas fa-file-pdf"></i></a>
    </li>
</ul>
<x-mfw::modal :route="route('panel.mailer', ['type' => 'refund', 'identifier' => $data->uuid])"
              title="Envoyer l'avoir par e-mail ?"
              :params="['uuid' => $data->uuid]"
              class="sendrefundbymail"
              question=""
              reference="send_by_mail_{{ $data->id }}"/>
