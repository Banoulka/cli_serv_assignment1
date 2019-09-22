$(document).ready(function(){
    let notifButton = $(".notify-drop");
    let notifBox = $("#notif-col");
    let adShown = false;

    // Enable all tooltips
    $('[data-toggle="tooltip"]').tooltip({
        trigger: "hover"
    });

    // Enable the manual notification tooltip
    notifButton.tooltip({
        trigger: "manual"
    });

    $("body").click((e) => {
        if(adShown) {
            disableAd();
        }
    });

    // Manual Tooltip control
    notifButton.hover((e) => {
        // Hover in
        if(!adShown) {
            notifButton.tooltip("show");
        }
    }, (e) => {
        // Hover out
        notifButton.tooltip("hide");
    });

    // Clicking the notification bell button
    notifButton.click((e) => {
        e.stopPropagation();
        if(adShown) {
            disableAd();
        } else {
            enableAd();
        }
    });

    function enableAd() {
        adShown = true;
        notifButton.tooltip("hide");
        notifBox.fadeIn(100);
        // $(".popover-body").scrollTop(0);
        $(".notify-drop a").addClass("active");
    }

    function disableAd() {
        adShown = false;
        notifBox.fadeOut(100);
        $(".notify-drop a").removeClass("active");
    }

    notifBox.click((e) => {
        // Stop the clicking of body if the mouse is
        // over the div of the notifBox
        e.stopPropagation();
    });

    // Stop the scrolling of the window if the mouse
    // is hovered over the notification box
    notifBox.hover(function() {
        let oldScrollPos = $(window).scrollTop();

        $(window).on('scroll.scrolldisabler', function(event) {
            $(window).scrollTop(oldScrollPos);
            event.preventDefault();
        });
    }, function() {
        $(window).off('scroll.scrolldisabler');
    });

    $("#sign-to-login").click((e) => {
        $("#signup").slideUp(() => {
            $("#login").slideDown();
        });
    });

    $("#login-to-sign").click((e) => {
        $("#login").slideUp(() => {
            $("#signup").slideDown();
        });
    });

    $("#title").keyup((e) => {
        checkLength("title", 20);
    });

    $("#description").keyup((e) => {
        checkLength("description", 270);
    });

    $("#content").keyup((e) => {
        checkLength("content", 1500);
    });

});

function checkLength(id, number) {
    // Get the length of the input and the "chars" span number
    let length = $("#" + id)[0].value.length;
    let chars = $("#" + id + "-chars")[0];

    // Make the number text equal to the number - the length of the input
    chars.innerHTML = number - length;

    // Find the full p tag after the id
    let tag = $("#" + id + " + .chars");

    // If the length is the max amount then add the red class
    if(length === number && !tag.hasClass("red")) {
        tag.addClass("red");
    } else if (length !== number) {
        tag.removeClass("red");
    }
}