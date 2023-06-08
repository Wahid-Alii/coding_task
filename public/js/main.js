var skeletonId = 'skeleton';
var contentId = 'content';
var skipCounter = 0;
var limit = 15;

function ajaxGet(url, type, onSuccessFunction, isBeforeSend = 1, connectionId = 0) {
  $.ajax({
    url: url,
    type: type,
    dataType: 'json',
    beforeSend: isBeforeSend ? beforeSend : () => null,
    success: function (response) {
      onSuccessFunction(response)
    }
  })
}

function ajaxPost(url, type, data, onSuccessFunction) {
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  })
  $.ajax({
    url: url,
    type: type,
    data: data,
    dataType: 'json',
    success: function (response) {
      onSuccessFunction(response)
    }
  })
}


function getRequests(mode) {
  ajaxGet(`/get-request?limit=${limit}&skip=${skipCounter}&mode=${mode}`, 'get', renderIndex)
}

function getMoreRequests(mode) {
  // Optional: Depends on how you handle the "Load more"-Functionality
  // your code here...
}

function getConnections() {
  ajaxGet(`/get-connections?limit=${limit}&skip=${skipCounter}`, 'get', renderIndex)
}

function getMoreConnections() {
  // Optional: Depends on how you handle the "Load more"-Functionality
  // your code here...
}

function getConnectionsInCommon(connectionId) {
  ajaxGet(`/get-connections-in-common?connectionId=${connectionId}&limit=${limit}&skip=${skipCounter}`, 'get', renderInnerIndex, 0, connectionId)
}

function getMoreConnectionsInCommon(userId, connectionId) {
  // Optional: Depends on how you handle the "Load more"-Functionality
  // your code here...
}

function getSuggestions() {
  ajaxGet(`/get-suggestions?limit=${limit}&skip=${skipCounter}`, 'get', renderIndex)
}

function renderIndex(response) {
  $("#skeleton").addClass('d-none')
  $("#content").empty()
  $("#content").html(response['data'])
  $("#content").removeClass('d-none')

  response['count'] < limit ? $("#load_more_btn_parent").addClass('d-none') : $("#load_more_btn_parent").removeClass('d-none')
}

function renderInnerIndex(response) {
  
  $("#skeleton").addClass('d-none')
  $("#content").empty()
  $("#content").html(response['data'])
  $("#content").removeClass('d-none')

  response['count'] < limit ? $("#load_more_btn_parent").addClass('d-none') : $("#load_more_btn_parent").removeClass('d-none')
}

function reRenderIndex(response) {
  $("#skeleton").addClass('d-none')
  $("#content").append(response)
  $("#content").removeClass('d-none')
  let displayedSuggestions = $("#content > div").length;
  console.log(displayedSuggestions)
  displayedSuggestions == 100 ? $("#load_more_btn_parent").addClass('d-none') : $("#load_more_btn_parent").removeClass('d-none');
}

function sendConnectionRequest(response) {
  if(response.status == 1) {
    getSuggestions()
  }
}

function deleteConnectionRequest(response) {
  alert(response.message)
  if(response.status == 1) {
    getRequests('sent')
  }
}

function acceptConnectionRequest(response) {
  alert(response.message)
  if(response.status == 1) {
    getRequests('received')
  }
}

function removeConnectionRequest(response) {
  alert(response.message)
  if(response.status == 1) {
    getConnections()
  }
}

function getMoreSuggestions() {
  let displayedSuggestions = $("#content > div").length;
  ajaxGet(`/get-suggested-connections?limit=${limit}&skip=${displayedSuggestions}`, 'get', reRenderIndex)
}

function sendRequest(connectionId) {
  let data = {
    connectionId: connectionId
  }
  ajaxPost(`/send-connection-request`, 'post', data, sendConnectionRequest)
}

function deleteRequest(connectionId) {
  let data = {
    connectionId: connectionId
  }
  ajaxPost(`/delete-connection-request`, 'post', data, deleteConnectionRequest)
}

function acceptRequest(connectionId) {
  let data = {
    connectionId: connectionId
  }
  ajaxPost(`/accept-connection-request`, 'post', data, acceptConnectionRequest)
}

function removeConnection(connectionId) {
  let data = {
    connectionId: connectionId
  }
  ajaxPost(`/remove-connection-request`, 'post', data, removeConnectionRequest)
}

function beforeSend() {
  console.log('before Send')
  $("#content").addClass('d-none')
  $("#skeleton").removeClass('d-none')
}

$(function () {
  $('#load_more_btn').attr('onclick', 'getMoreSuggestions()')
  getSuggestions();
});