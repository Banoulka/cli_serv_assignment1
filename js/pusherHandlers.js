/**
 * Function to handle a new message to the logged in user
 * @param data
 */
const newMessageHandler = data => {
    const { userFrom } = data;

    // console.log("new message from " + userFrom);
    // Update the slimfos if the user is currently looking at
    // somebody elses messages, there to put recent messages at the top
    // like a real app
    if (toUserId !== userFrom) {
        displaySlimfos();
    }
    let audio = new Audio("../sounds/message.wav");
    audio.play();
};

/**
 * Function to handle a new notification from the notification channel
 * @param data
 */
const newNotificationHandler = data => {
    // Play the audio message
    let audio = new Audio("../sounds/message.wav");
    audio.play();

    let { notif } = data;

    // Add the notification to the list
    let htmlString = `
                    <a href="${notif.link}&notif_id=${notif.id}" class="notification-link">
                        <div class="notification d-flex flex-row align-items-center notification-unread">
                            <img src="${notif.user_from.display_pic}" class="notif-profile-pic" alt="">
                            <p><span class="notif-profile-name">${notif.user_from.name}</span> ${notif.message}</p>
                            <div class="time-icon">
                                <i class="${notif.icon}"></i>
                                <span class="time-text">${notif.time}</span>
                            </div>
                        </div>
                    </a>`;

    // Prepend the notification
    htmlString += notifList.innerHTML;
    notifList.innerHTML = htmlString;

    // Update the notifcation unread count
    unread++;
    unreadCount.innerText = unread > 99 ? "99+" : unread
};