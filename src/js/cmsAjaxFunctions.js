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

function toggleOrgBlacklist(isEnabled,orgID){
    if(isEnabled){
        $.ajax({
            type: "POST",
            data: {
                orgID: orgID,
            },
            url: "/php/contentManagementSystem/Content-Management-System/src/php/adminControls/orgAdminEnableBlacklist.php",
            success(){
                successFunction("Enabled Blacklist!");
            }
        });
    }else if(!isEnabled){
        $.ajax({
            type: "POST",
            data: {
                orgID: orgID,
            },
            url: "/php/contentManagementSystem/Content-Management-System/src/php/adminControls/orgAdminDisableBlacklist.php",
            success(){
                successFunction("Disabled Blacklist!");
            }
        });
    }
}

function toggleOrgPostApproval(isEnabled,orgID){
    if(isEnabled){
        $.ajax({
            type: "POST",
            data: {
                orgID: orgID,
            },
            url: "/php/contentManagementSystem/Content-Management-System/src/php/adminControls/orgAdminEnablePostApproval.php",
            success(){
                successFunction("Enabled Post Approval!");
            }
        });
    } else if(!isEnabled){
        $.ajax({
            type: "POST",
            async: false,
            data: {
                orgID: orgID,
            },
            url: "/php/contentManagementSystem/Content-Management-System/src/php/dataManagement/checkForPendingPosts.php",
            success: async function () {
                $.ajax({
                    url:"/php/contentManagementSystem/Content-Management-System/src/php/responseData/checkIfPendingPostsExistResponse.php",
                    cache:false,
                    success:function(data){
                        if(data == "0"){
                            alertify.confirm('Disable Post Approval', 'There are still pending posts in queue. If you disable this feature, those posts will be made public. Are you sure you want to continue?',
                                function(){
                                    $.ajax({
                                        type: "POST",
                                        data: {
                                            orgID: orgID,
                                        },
                                        url: "/php/contentManagementSystem/Content-Management-System/src/php/adminControls/orgAdminDisablePostApproval.php",
                                        success(){
                                            successFunction("Disabled Post Approval!");
                                        }
                                    });
                                },function(){

                                });
                        }else{
                            $.ajax({
                                type: "POST",
                                data: {
                                    orgID: orgID,
                                },
                                url: "/php/contentManagementSystem/Content-Management-System/src/php/adminControls/orgAdminDisablePostApproval.php",
                                success(){
                                    successFunction("Disabled Post Approval!");
                                }
                            });
                        }
                    }
                });
            }
        });

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

function orgAdminDeletePost(postID) {
    alertify.confirm('Before You Delete', 'Are you sure you wish to delete this post? There is no way to get it back after.',
        function(){
            $.ajax({
                type: "POST",
                async: false,
                data: {
                    postID: postID
                },
                url: "/php/contentManagementSystem/Content-Management-System/src/php/adminControls/orgAdminDeletePost.php",
                success: async function () {
                    await successFunction("Deleted Successfully!");
                },
            });
        },function(){

        });
}

function deleteUserByBlockMsgID(blockID){
    alertify.confirm('<p>Deleting an account cannot be undone. Are you sure?</p>', function(e){
        if(e){
            $.ajax({
                type: "POST",
                data: {
                    msgID: blockID,
                },
                url: "/php/contentManagementSystem/Content-Management-System/src/php/adminControls/orgAdminDeletePost.php",
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
function checkBanUserExists(username){
    $.ajax({
        type: "POST",
        async: false,
        data: {
            username: username,
        },
        url: "/php/contentManagementSystem/Content-Management-System/src/php/dataManagement/checkIfUserExists.php",
        success: async function () {
            $.ajax({
                url:"/php/contentManagementSystem/Content-Management-System/src/php/responseData/checkIfUserExistsResponse.php",
                cache:false,
                success:function(data){
                    if(data == "0"){
                        successFunction("Banned User!");
                        document.getElementById('banUserModalForm').submit();
                    }else{
                        errorFunction("User not found!",'');
                    }
                }
            });
        }
    });
}
function orgAdminUnBanUser(userID,orgID){
    alertify.confirm('Unban User', 'Are you sure you wish to un ban this user? This will allow them to see the organization and interact with content once again.',
        function(){
            $.ajax({
                type: "POST",
                async: false,
                data: {
                    userID: userID,
                    orgID: orgID
                },
                url: "/php/contentManagementSystem/Content-Management-System/src/php/adminControls/orgAdminUnBanUser.php",
                success: async function () {
                    await successFunction("User Unbanned!");

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

function orgAdminAllowOrgPost(postID){
    $.ajax({
        type: "POST",
        async: false,
        data: {
            postID: postID
        },
        url: "/php/contentManagementSystem/Content-Management-System/src/php/adminControls/orgAdminAllowOrgPost.php",
        success: async function () {
            successFunction("Post Allowed!");
        }
    });
}

function orgAdminDenyOrgPost(postID){
    $.ajax({
        type: "POST",
        async: false,
        data: {
            postID: postID
        },
        url: "/php/contentManagementSystem/Content-Management-System/src/php/adminControls/orgAdminDenyOrgPost.php",
        success: async function () {
            successFunction("Post Denied!");
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
        url: "/php/contentManagementSystem/Content-Management-System/src/php/dataManagement/whitelistManager.php",
        success: async function () {
            successFunction("Word Added to Whitelist");
        }
    });


}

function addToOrgBlacklist(word, orgID) {
    $.ajax({
        type: "POST",
        async: false,
        data: {
            word: word,
            orgID: orgID
        },
        url: "/php/contentManagementSystem/Content-Management-System/src/php/dataManagement/orgBlacklistManager.php",
        success: async function () {
            successFunction("Word Added to Blacklist");
        }
    });

}

function appealBlock(postID) {
    alertify.confirm('Appeal Blocked Message','<p>Are you sure you wish to appeal this post?</p><p>Please Provide A Reason:</p>' +
        '<input type="text" id="appealReason" placeholder="Why this should be allowed">', function() {
            if (document.getElementById('appealReason').value.trim() !== "") {
                $.ajax({
                    type: "POST",
//                async: false,
                    data: {
                        postID: postID,
                        reason: document.getElementById('appealReason').value
                    },
                    url: "/php/contentManagementSystem/Content-Management-System/src/php/dataManagement/appealPost.php",
                    success: async function () {
                        await successFunction("Appeal Sent!", '');
                    }
                });
            }
        },
        function () {

        });
}