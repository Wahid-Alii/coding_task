<div class="row justify-content-center mt-5">
  <div class="col-12">
      <div class="card shadow  text-white bg-dark">
          <div class="card-header">Coding Challenge - Network connections</div>
          <div class="card-body">
              <div class="btn-group w-100 mb-3" role="group" aria-label="Basic radio toggle button group">
                  <input type="radio" class="btn-check" name="btnradio" id="btnradio1" autocomplete="off" checked>
                  <label class="btn btn-outline-primary" for="btnradio1" onclick="getSuggestions()" id="get_suggestions_btn">Suggestions
                      ({{ $suggestedUsersCount }})</label>

                  <input type="radio" class="btn-check" name="btnradio" id="btnradio2" autocomplete="off">
                  <label class="btn btn-outline-primary" for="btnradio2" onclick="getRequests('sent')" id="get_sent_requests_btn">Sent Requests
                      ({{ $pendingSendRequestCount }})</label>

                  <input type="radio" class="btn-check" name="btnradio" id="btnradio3" autocomplete="off">
                  <label class="btn btn-outline-primary" for="btnradio3" onclick="getRequests('received')" id="get_received_requests_btn">Received
                      Requests({{ $pendingReceivedConnectionsCount }})</label>

                  <input type="radio" class="btn-check" name="btnradio" id="btnradio4" autocomplete="off">
                  <label class="btn btn-outline-primary" for="btnradio4" onclick="getConnections()" id="get_connections_btn">Connections
                      ({{ $connectedUsersCount }})</label>
              </div>
              <hr>
              <div id="content" class="d-none">
              </div>
              <div id="skeleton" class="d-none">
                  @for ($i = 0; $i < 10; $i++)
                      <x-skeleton />
                  @endfor
              </div>

              <div class="d-flex justify-content-center mt-2 py-3" id="load_more_btn_parent">
                <button class="btn btn-primary" onclick="loadMoreData()" id="load_more_btn">Load more</button>
              </div>
          </div>
      </div>
  </div>
</div>


