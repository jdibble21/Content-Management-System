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

function resolve(msgID,type,option) {
    if(option == "Delete User"){
        deleteUserByBlockMsgID(msgID);
    }
    if(option == "Allow"){
        allowPost(msgID);
    }
    if(option == "Delete Post"){
        adminDeletePost(msgID);
    }
}

function deleteUserByBlockMsgID(blockID){
    alertify.confirm('<p>Deleting an account cannot be undone. Are you sure?</p>', function(e){
        if(e){
            $.ajax({
                type: "POST",
                data: {
                    msgID: blockID,
                },
                url: "/php/contentManagementFilter/adminControls/deleteUserFromPostID.php",
                success(){
                    successFunction("Account Deleted!");
                }
            });
        }

    })

}
function adminDeletePost(msgID){
    alertify.confirm('Before You Delete', 'Are you sure you wish to delete this post? There is no way to get it back after.',
        function(){
            $.ajax({
                type: "POST",
                async: false,
                data: {
                    msgID: msgID,
                },
                url: "/php/contentManagementSystem/Content-Management-System/src/php/adminControls/deletePost.php",
                success: async function () {
                    await successFunction("Deleted Successfully!");

                },
            });
        },function(){

        });
}

function allowPost(blockID){
    $.ajax({
        type: "POST",
        data: {
            msgID: blockID,
        },
        url: "/php/contentManagementFilter/adminControls/allowUserPost.php",
        success(){
            alertify.alert("Post Allowed");
        }
    });
}

function addWordToBlacklist(word){
    $.ajax({
        type: "POST",
        async: false,
        data: {
            word: word
        },
        url: "/php/contentManagementSystem/Content-Management-System/src/php/dataManagement/blacklistManager.php",
        success: async function () {
            successFunction("Word Added to Blacklist");
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
            successFunction("Word Added to Whitelist");
        }
    });
}