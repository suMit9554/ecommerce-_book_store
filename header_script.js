document.getElementById('menu-btn').addEventListener('click', function() {
    document.querySelector('.navbar').classList.toggle('active');
});

document.getElementById('profile-icon').addEventListener('click', function() {
    document.getElementById('account-box').classList.toggle('show');
});

document.addEventListener('click', function(event) {
    let profileContainer = document.querySelector('.profile-container');
    let accountBox = document.getElementById('account-box');
    if (!profileContainer.contains(event.target)) {
        accountBox.classList.remove('show');
    }
});
