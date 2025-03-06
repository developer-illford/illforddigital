// ********************************************************************************************
//CAREERS SECTION ADMIN PANNEL
// ********************************************************************************************

// variable decerations
var jobId, vacancyName, JobDescriptioon, Experience, DeadLine;

//function to read careers form data
function readFomV() {
    jobId = document.getElementById("job_id").value;
    vacancyName = document.getElementById("vacancy_name").value;
    NoOfvacancies = document.getElementById("no_of_vacancies").value;
    JobDescriptioon = document.getElementById("job_description").value;
    Experience = document.getElementById("experience").value;
    DeadLine = document.getElementById("deadline").value;
    console.log(jobId, vacancyName, JobDescriptioon, Experience, DeadLine);
}

function clearVaccancyForm() {
    event.preventDefault();
    document.getElementById("job_id").value = "";
    document.getElementById("vacancy_name").value = "";
    document.getElementById("no_of_vacancies").value = "";
    document.getElementById("job_description").value = "";
    document.getElementById("experience").value = "";
    document.getElementById("deadline").value = "";
}


//function to add vacancy data 
document.getElementById("createV").onclick = function () {
    readFomV();

    if (jobId && vacancyName && JobDescriptioon && Experience && DeadLine) {
        alert('Are you sure to Update?')
        firebase
            .database()
            .ref("vacancies/" + jobId)
            .set({
                job_id: jobId,
                vacancy_name: vacancyName,
                no_of_vacancies: NoOfvacancies,
                job_description: JobDescriptioon,
                experience: Experience,
                deadline: DeadLine,
            });
        alert("New vacancy added");
        clearVaccancyForm();
    } else {
        alert("Please enter Full details to insert vacancy data.");
    }
};

//function to read vacancy data 
// document.getElementById("readV").onclick = function () {
//     readFomV();

//     if (jobId) {
//         firebase
//         .database()
//         .ref("vacancies/" + jobId)
//         .on("value", function (snap) {
//             document.getElementById("job_id").value = snap.val().job_id;
//             document.getElementById("vacancy_name").value = snap.val().vacancy_name;
//             document.getElementById("job_description").value = snap.val().job_description;
//             document.getElementById("experience").value = snap.val().experience;
//             document.getElementById("deadline").value = snap.val().deadline;
//         });
//     } else {
//         alert("Please enter a Job ID to read vacancy data.");
//     }
// };

//function to update vacancy data 
// document.getElementById("updateV").onclick = function () {
//     readFomV();

//     if (jobId, vacancyName, JobDescriptioon, Experience, DeadLine) {
//         firebase
//         .database()
//         .ref("vacancies/" + jobId)
//         .update({
//             job_id:jobId,
//             vacancy_name: vacancyName,
//             job_description: JobDescriptioon,
//             experience: Experience,
//             deadline: DeadLine,
//         });
//     alert("Vacancy Details Updated");
//     } else {
//         alert("Please enter a full details to update vacancy data.");
//     }


//     document.getElementById("job_id").value = "";
//     document.getElementById("vacancy_name").value = "";
//     document.getElementById("job_description").value = "";
//     document.getElementById("experience").value = "";
//     document.getElementById("deadline").value = "";
// };




//function to remove vacancy data 
// document.getElementById("deleteV").onclick = function () {
//     readFomV();

//     if (jobId) {
//         firebase
//             .database()
//             .ref("vacancies/" + jobId)
//             .remove();
//         alert("vacancy removed");
//     } else {
//         alert("Please enter a Job ID to delete vacancy data.");
//     }
//     document.getElementById("job_id").value = "";
//     document.getElementById("vacancy_name").value = "";
//     document.getElementById("job_description").value = "";
//     document.getElementById("experience").value = "";
//     document.getElementById("deadline").value = "";

// };

















// ********************************************************************************************
// Function to create the vacancies table with expand/collapse functionality
// ********************************************************************************************
function createVacanciesTable(snapshot) {
    // Clear previous content of the dummy_table div
    var tableDiv = document.querySelector(".vacancy_table");
    tableDiv.innerHTML = "";

    // Create a table element
    var table = document.createElement("table");
    table.classList.add("vacancies_table");

    // Create table header
    var thead = document.createElement("thead");
    var headerRow = document.createElement("tr");
    headerRow.innerHTML = "<th class='action-column'>Action</th><th>Job ID</th><th>Vacancy Name</th><th>No. Of Openings</th><th>Job Description</th><th>Experience</th><th>Deadline</th>";
    thead.appendChild(headerRow);
    table.appendChild(thead);

    // Create table body
    var tbody = document.createElement("tbody");

    // Iterate through vacancies data and populate the table
    snapshot.forEach(function (childSnapshot) {
        var vacancyKey = childSnapshot.key; // Get the key of the job
        var vacancyData = childSnapshot.val();
        var row = document.createElement("tr");
        row.innerHTML = "<td><button class='edit_btn' data-key='" + vacancyKey + "'><i class='fa-solid fa-pen-to-square'></i>&nbsp;&nbsp;Edit</button><br><button class='delete_btn' data-key='" + vacancyKey + "'><i class='fa-solid fa-trash'></i>&nbsp;&nbsp;Delete</button></td>" +
            "<td>" + vacancyData.job_id + "</td>" +
            "<td>" + vacancyData.vacancy_name + "</td>" +
            "<td>" + vacancyData.no_of_vacancies + "</td>" +
            "<td class='job_description_cell'>" +
            "<div class='job_description_content'>" + vacancyData.job_description + "</div>" +
            "<button class='expand_collapse_btn'>Expand/Collapse</button>" +
            "</td>" +
            "<td>" + vacancyData.experience + "</td>" +
            "<td>" + vacancyData.deadline + "</td>";

        tbody.appendChild(row);
    });

    // Add event listener for expand/collapse button click, edit button click, and delete button click
    tbody.addEventListener("click", function (e) {
        if (e.target && (e.target.classList.contains("edit_btn") || e.target.parentNode.classList.contains("edit_btn"))) {
            // If edit button or its child element is clicked
            var button = e.target.classList.contains("edit_btn") ? e.target : e.target.parentNode;
            var key = button.getAttribute("data-key");
            var jobRef = firebase.database().ref("vacancies").child(key);
            jobRef.once("value", function (snapshot) {
                var jobData = snapshot.val();
                // Fill form fields with job data
                document.getElementById("job_id").value = jobData.job_id;
                document.getElementById("job_id").readOnly = true;
                document.getElementById("vacancy_name").value = jobData.vacancy_name;
                document.getElementById("no_of_vacancies").value = jobData.no_of_vacancies;
                document.getElementById("job_description").value = jobData.job_description;
                document.getElementById("experience").value = jobData.experience;
                document.getElementById("deadline").value = jobData.deadline;
                // Set the key of the job being edited
                document.getElementById("edit_job_key").value = key;
            });
        } else if (e.target && e.target.classList.contains("delete_btn")) {
            // If delete button is clicked
            var key = e.target.getAttribute("data-key");
            if (confirm("Are you sure you want to delete this vacancy?")) {
                // If user confirms deletion, remove data from the database
                var jobRef = firebase.database().ref("vacancies").child(key);
                jobRef.remove()
                    .then(function () {
                        // Data successfully deleted
                        console.log("Vacancy deleted successfully");
                    })
                    .catch(function (error) {
                        // Error occurred while deleting data
                        console.error("Error deleting vacancy: ", error);
                    });
            }
        } else if (e.target && e.target.classList.contains("expand_collapse_btn")) {
            var descriptionContent = e.target.previousElementSibling;
            descriptionContent.classList.toggle("expanded");
            e.target.textContent = descriptionContent.classList.contains("expanded") ? "Collapse" : "Expand";
        }
    });

    table.appendChild(tbody);
    tableDiv.appendChild(table);
}

// Call the function to display vacancies when the page is loaded
document.addEventListener("DOMContentLoaded", function () {
    var vacanciesRef = firebase.database().ref("vacancies");
    vacanciesRef.once("value", function (snapshot) {
        createVacanciesTable(snapshot);
    });
});


