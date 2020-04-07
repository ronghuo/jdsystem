$('#userAPPImg').qrcode({
    render: "canvas",
    width: 128,
    height: 128,
    text: user_app_link
});
$('#managerAPPImg').qrcode({
    render: "canvas",
    width: 128,
    height: 128,
    text: manager_app_link
});