






websocket_response=function(data){
    switch(data.type) {
        case "PONG":
            // code block
            setupPing(data)
            break;
        case "RESULT":
            // code block
            setupProcess(data)
            break;
        default:
        // code block

    }
}


webSocket_check_osbox=function( host,port ){
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
        console.log("Connection to osbox failed");
        //console.log(evt);

        //webSocket_test_osboxmaster();
        //webSocket_onError(evt)
    };

    return osboxwebsocket
}








init = function(){

  // check if loggedin
  // /api/status
    fetch("/api/status")
        .then((response) => {
            return response.json();
        })
        .then((data) => {
            console.log("network1 response");
            //console.log(data.data[1]);
            //$('#detectTitleText').html(data.data[1]);
            PhaseTwo_install_core();

            //network2();



        }).catch(function(){

        console.log("--------------");
    });

  // Load devices registered to user

  //

  //# do osbpx ping



};




$("document").ready(function(){
    init();

});
