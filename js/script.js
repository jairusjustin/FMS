

// Add hovered class to selected list item
let list = document.querySelectorAll(".my-navigation li");

function activeLink() {
    list.forEach((item) => {
        item.classList.remove("hovered");
    });
    this.classList.add("hovered");
}

list.forEach((item) => item.addEventListener("mouseover", activeLink));

// Menu Toggle
let toggle = document.querySelector(".my-toggle");
let navigation = document.querySelector(".my-navigation");
let main = document.querySelector(".my-main");

toggle.onclick = function () {
    navigation.classList.toggle("active");
    main.classList.toggle("active");
};


function toggleButtons(id, type) {
    var buttonsDiv;
    if (type === "crop") {
        buttonsDiv = $('#cropActionButtons_' + id);
    } else if (type === "field") {
        buttonsDiv = $('#fieldActionButtons_' + id);
    }

    // Hide all other action button divs
    $('.my-action-buttons').not(buttonsDiv).hide();
    
    if (buttonsDiv.is(":visible")) {
        buttonsDiv.hide();
    } else {
        buttonsDiv.show();
    }
}

function toggleActions(event) {
    // Find the action buttons container
    const actionButtons = event.target.closest('.my-action-icons').querySelector('.action-buttons');

    // Toggle the visibility of the action buttons
    if (actionButtons.classList.contains('hidden')) {
        actionButtons.classList.remove('hidden');
    } else {
        actionButtons.classList.add('hidden');
    }
}

