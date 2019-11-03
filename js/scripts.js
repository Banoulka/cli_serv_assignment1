$(document).ready(function(){

    setupNotificationEvents();
    setupCharLimiters();
    setupTagPicker();
    setupOverlayNav();
    setupTagCheckButtons();
    setMinifiedForm();
});

function setupCharLimiters() {
    $("#title").keyup((e) => {
        checkLength("title", 19);
    });

    $("#description").keyup((e) => {
        checkLength("description", 220);
    });

    $("#content").keyup((e) => {
        checkLength("content", 1500);
    });

    function checkLength(id, number) {
        // Get the length of the input and the "chars" span number
        let input = $("#" + id);
        let length = input[0].value.length;
        let chars = $("#" + id + "-chars")[0];

        // Make the number text equal to the number - the length of the input
        chars.innerHTML = number - length;

        // Find the full p tag after the id
        let tag = $("#" + id + " + .chars");

        // If the length is the max amount then add the red class
        if(length > number && !tag.hasClass("red")) {
            tag.addClass("red");
            input.removeClass("is-valid");
            input.addClass("is-invalid");
        } else if (length === 0 ) {
            tag.removeClass("red");
            input.removeClass("is-invalid");
            input.removeClass("is-valid");
        } else if (length <= number) {
            tag.removeClass("red");
            input.removeClass("is-invalid");
            input.addClass("is-valid");
        }
    }
}

function setupNotificationEvents() {
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
}

function setupTagPicker() {
    // <div class="picked-option"><span>Action</span><i class="fas fa-minus"></i></div>
    let tagSelector = $("#tags");
    let tagLimitChar = $("#tag-limits");
    let canSelect = true;
    let tags = [];
    console.log("tag change");

    tagSelector.change((e) => {
        e.preventDefault();
        // Get the tag value
       let tag = tagSelector.val();
        // If tag isnt the default option and tag doesn't already exist in array
       if(tag !== "Select a tag" && tags.indexOf(tag) === -1) {
            // Add a new div with the value tag and the label tag.
           $("#tag-list").append("<div id=\"" + tag + "box" + "\" class=\"picked-option\"><span>" + tag + "</span><i id=\"" + tag + "-tag\" class=\"fas fa-minus minus-tag\"></i></div>");
           // Add to the array of tags
           tags.push(tag);

           // Check the associated tag box
           $("#" + tag).prop("checked", true);

           // Add the event to remove on the little minus click
           $("#" + tag + "-tag").click((e) => {
               $("#" + tag + "box").remove();
               tags.splice( tags.indexOf(tag), 1);
               checkTag();
               // Take the check box off of the check
               $("#" + tag).prop("checked", false);
           });
            checkTag();
       }
        tagSelector.val("Select a tag");
    });

    function checkTag() {
        let tagLimitP = $("#tag-limit-p");
        if(tags.length === 4) {
            tagSelector.attr("disabled", true);
            canSelect = false;
            tagLimitP.addClass("red");
        } else if(tags.length < 4 && canSelect === false ) {
            tagSelector.attr("disabled", false);
            canSelect = true;
            tagLimitP.removeClass("red");
        }
        tagLimitChar[0].innerHTML = 4 - tags.length;
    }
}

function setupOverlayNav() {

    $("#sidebar-open").on("click", function () {
        // Fade in sidebar
        $(".overlay").addClass("active");
        $(".collapse-sidebar").addClass("active");

        // Figure out what needs to be moved
        if($(window).width() < 1200) {

            // Move the tags
            let tagContent = $("#tag-content").detach();
            tagContent.appendTo("#tags-sidebar-section");

            // Move the tag check buttons
            let tagButtons = $("#check-buttons-content").detach();
            tagButtons.appendTo("#tags-sidebar-section");
        }

        if($(window).width() < 992) {
            // Move the filters
            let filterContent = $("#filter-content").detach();
            filterContent.appendTo("#filters-sidebar-section");

            // Move the sort by content
            let sortContent = $("#sort-content").detach();
            sortContent.appendTo("#sortby-sidebar-section");
        }
    });

    $("#dismiss-sidebar, .overlay").on("click", function() {
        $(".collapse-sidebar").removeClass("active");
        $(".overlay").removeClass("active");

        let tagContent = $("#tag-content").detach();
        tagContent.appendTo("#tag-row-main");

        let filterContent = $("#filter-content").detach();
        filterContent.appendTo("#filter-row-main");

        let sortByContent = $("#sort-content").detach();
        sortByContent.appendTo("#sort-row-main");

        let tagButtons = $("#check-buttons-content").detach();
        tagButtons.appendTo("#check-buttons-row");
    });
}

function setupTagCheckButtons () {
    $("#check-all-tags").click(function(e) {
        $(".custom-checkbox").each(function() {
            this.children[0].checked = true;
        });
    });

    $("#uncheck-all-tags").click(function(e) {
        $(".custom-checkbox").each(function() {
            this.children[0].checked = false;
        });
    });
}

function setMinifiedForm () {
    $("#mini-checkbox").click((e) => {
        let hiddenCheck = $("#hidden-checkbox");
        if(hiddenCheck.is(":checked")) {
            console.log("checked");
            hiddenCheck.prop("checked", false);
        } else {
            console.log("not checked.");
            hiddenCheck.prop("checked", true);
        }
        $("#minified-form").submit();
    });
}