
@foreach ($connected as $item)
    <div class="my-2 shadow text-white bg-dark p-1" id="">
        <div class="d-flex justify-content-between">
            <table class="ms-1">
                <td class="align-middle">{{ $item->name }}</td>
                <td class="align-middle"> - </td>
                <td class="align-middle">{{ $item->email }}</td>
                <td class="align-middle">
            </table>
            <div>
                <button style="width: 220px" id="get_connections_in_common_{{ $item->id }}" class="btn btn-primary"
                    type="button" data-bs-toggle="collapse" data-bs-target="#collapse_{{ $item->id }}"
                    aria-expanded="false" aria-controls="collapseExample"
                    onclick="getConnectionsInCommon('{{ $item->id }}')">
                    Connections in common ({{ $item->commonUsers }})
                </button>
                <button id="create_request_btn_{{ $item->id }}" class="btn btn-danger me-1"
                    onclick="removeConnection('{{ $item->id }}')">Remove Connection</button>
            </div>

        </div>
        <div class="collapse" id="collapse_{{ $item->id }}">
            <div id="content_{{ $item->id }}" class="p-2">
            </div>
            <div class="d-flex justify-content-center w-100 py-2">
                <button class="btn btn-sm btn-primary" id="">Load
                    more</button>
            </div>
        </div>
    </div>
@endforeach
