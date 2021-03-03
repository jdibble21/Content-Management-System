/* This file holds ajax functions for sending and receiving content management related data*/

async function successFunction(string,reload='reload') {
    alertify.message("<div style='text-align: center'><img class='avatar' src='/assets/img/LogoB.png'><h1>" + string + "</h1></div>");
    await new Promise(r => setTimeout(r, 500));
    if (reload==='reload') {
        location.reload();
    }
    else if (reload === '') {
    }
    else {
        location.href=reload;
    }
}

async function errorFunction(string,reload='reload') {
    alertify.error("<div style='text-align: center'><img class='avatar' src='/assets/img/LogoB.png'><h1>" + string + "</h1></div>");
    await new Promise(r => setTimeout(r, 750));
    if (reload==='reload') {
        location.reload();
    }
    else if (reload === '') {
        //do nothing
    }
    else {
        location.href=reload;
    }
}

function addWordToBlacklist(word){
    $.ajax({
        type: "POST",
        async: false,
        data: {
            word: word
        },
        url: "/src/php//blacklistManager.php",
        success: async function () {
            await successFunction("Word Added");
        }
    });
}

function addWordToWhitelist(word){
    $.ajax({
        type: "POST",
        async: false,
        data: {
            word: word
        },
        url: "/php/contentManagementFilter/whitelistManager.php",
        success: async function () {
            $.ajax({
                url:"/php/contentManagementFilter/varStorage/whitelistWordAddStorage.php",
                cache:false,
                success:function(data){

                    if(data == "0"){
                        successFunction("Word Added");
                    }else{
                        errorFunction("Word already exists!",'');
                    }
                }
            })
        }
    });
}