






websocket_response=function(data,osboxwebsocket){

    console.log("command:" +data.text);

    switch(data.text) {
        case "osbox status":
            if( data.code==200 ){
                let row = $('.devices-row');
                let col = $('<div>').addClass('col-md4').appendTo(row);
                createCardsFor(col,data.data,data.host);
            }
            if( data.code==500 ){

            }
            break;
        case "osbox auth request":
            if( data.code==200 ){
                data.user_code;
                data.verification_uri;
                data.verification_uri_complete;
                console.log(data.data);



                window.activation = Swal.fire({
                    title: '<strong>'+data.data.user_code+'</strong>',
                    icon: 'info',
                    html:
                        'Vul de bovenstaande <b>code</b> in op ' +
                        '<a target="_blank" href="'+data.data.verification_uri_complete+'">deze</a> ' +
                        'pagina',
                    showConfirmButton: false,
                    showCloseButton: false,
                    showCancelButton: false,
                    focusConfirm: false,
                    allowOutsideClick: false,


                });

                osboxwebsocket.send("osbox authpoll");


            }
            break;
        case "osbox auth poll":
            if( data.code==200 ){
                data.data.userId;
                console.log(data.data);



                window.activation.close();
                window.location.reload();

                //osboxwebsocket.send("osbox authpoll");


            }
            break;
        default:
            // code block

    }
}


webSocket_poll_osbox=function( host,port ){
    //console.log("webSocket_test_osbox");
    osboxwebsocket = new WebSocket("ws://"+host+":"+port+"/");

    osboxwebsocket.onopen = function(evt) {
        console.log("Attempt to connect to osbox");
        //webSocket_writeToScreen("CONNECTED");
        message = "osbox";
        //webSocket_writeToScreen("SENT: " + message);
        osboxwebsocket.send(message);
    };

    osboxwebsocket.onclose = function(evt) {
        console.log("Connection to osbox closed ");
        //connectAttempts();
        //console.log(wserrors[evt.code]);
        //webSocket_writeToScreen("DISCONNECTED");
    };

    osboxwebsocket.onmessage = function(evt) {
        //console.log("onmessage:");

        jsondata = JSON.parse(evt.data)
        var RespData = {}
        RespData.type=jsondata[0]
        RespData.ts=jsondata[1]
        RespData.code=jsondata[2]
        RespData.text=jsondata[3]
        RespData.data=jsondata[4]

        websocket_response(RespData);

    };

    osboxwebsocket.onerror = function(evt) {
        console.log("Connection to osbox failed");
        //console.log(evt);

        //webSocket_test_osboxmaster();
        //webSocket_onError(evt)
    };

    return osboxwebsocket
}



unregistered_devices = function (){

    fetch("/api/unregistereddevice")
        .then((response) => {
            return response.json();
        })
        .then((data) => {
            console.log(data);
            //sessionStorage.setItem("token", data.token);
            //$('#detectTitleText').html(data.data[1]);
            //PhaseTwo_install_core();

            //network2();



        }).catch(function(){

        console.log("--------------");
    });
}




configureDevice = function(device,host){
    console.log(device);
    console.log(host);
    device.send("osbox auth request")

}

function createCardsFor($container,device,host,show='unregistered') {
    console.log("------------------------------------------")
    //console.log(device);
    //alert(device.hardware);
    if(show=='unregistered' && device['device-state'] == 'registered'){
        return
    }else if(show=='registered' && device['device-state'] == 'unregistered'){
        return
    }


    let card = $('<div>').addClass('card').css({
        'width': '18rem',
        'margin': '2px',
    }).appendTo($container);
    //let cardHeader = $('<div>').addClass('card-header').appendTo(card);
    //let cardImage = $('<img>').attr({
    //    alt: "alt",
    //    src: "https://via.placeholder.com/50"
    //}).appendTo(cardHeader);
    //let deleteButton = $('<button>').addClass('btn btn-sm btn-outline-danger').text('Delete').on('click', deleteButtonClick).appendTo(cardHeader);
    let cardBody = $('<div>').addClass('card-body').appendTo(card);
    let bodyTitle2 = $('<h5>').text(host).appendTo(cardBody);
    let bodyTitle = $('<h6>').addClass('card-subtitle mb-2 text-muted').text(device.hardware).appendTo(cardBody);
    let bodyTitle3 = $('<h7>').addClass('card-subtitle mb-2 text-muted').text("State: "+device['device-state']).appendTo(cardBody);

    let bodyText = $('<p>').addClass("card-text").text(device['sys-info']).appendTo(cardBody);
    if( sessionStorage.getItem("token") != "null" ){
        let bodyLink = $('<a href="#">>').addClass("card-link").text('Manage').appendTo(cardBody);
        let bodyLink2 = $('<a href="#" onclick="configureDevice(device,host)">').addClass("card-link").text('Configure').appendTo(cardBody);
    }

}






polldevice=function(host){
//console.log("webSocket_test_osbox");
    var osboxwebsocket = new WebSocket("wss://"+host+":81/");

    osboxwebsocket.onopen = function(evt) {
        console.log("Attempt to connect to osbox");
        //webSocket_writeToScreen("CONNECTED");
        message = "osbox status";
        //webSocket_writeToScreen("SENT: " + message);
        osboxwebsocket.send(message);
    };

    osboxwebsocket.onclose = function(evt) {
        console.log("Connection to osbox closed ");
        //connectAttempts();
        //console.log(wserrors[evt.code]);
        //webSocket_writeToScreen("DISCONNECTED");
    };

    osboxwebsocket.onmessage = function(evt) {
        console.log("onmessage:");

        jsondata = JSON.parse(evt.data)
        var RespData = {}
        RespData.host=host
        RespData.type=jsondata[0]
        RespData.ts=jsondata[1]
        RespData.code=jsondata[2]
        RespData.text=jsondata[3]
        RespData.data=jsondata[4]




        //sessionStorage.setItem(host, JSON.stringify(RespData.data));
        websocket_response(RespData,osboxwebsocket);



        //unconfigured_osboxfound()

        // h1#detectTitle
        // p#detectTitleText
        //console.log( jsondata);
        //message = "discover|osbox";
        //webSocket_writeToScreen("SENT: " + message);
        //osboxwebsocket.send(message);

        //osboxwebsocket.close();
    };

    osboxwebsocket.onerror = function(evt) {
        sessionStorage.setItem(host, "unreachable");
        console.log("Connection to osbox failed");
        //console.log(evt);

        //webSocket_test_osboxmaster();
        //webSocket_onError(evt)
    };

    return osboxwebsocket
}


dashboardinit = function(){
  // check if loggedin
  // /api/status
    ///api/unregistereddevice
    //sessionStorage.clear();
    fetch("/api/status")
        .then((response) => {
            return response.json();
        })
        .then((data) => {
            //console.log("network1 response");
            //console.log(data.token);
            sessionStorage.setItem("token", data.token);

            //let row = $('<div>').addClass('row').prependTo(document.body);
            let row = $('.devices-row').addClass('row');
            for( i in data.unregistered ){
                device = polldevice(data.unregistered[i]);

            }

        }).catch(function(){

            console.log("--------------");
        });

};




$("document").ready(function(){
    dashboardinit();

});
