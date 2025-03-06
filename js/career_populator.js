const vacanciesRef = firebase.database().ref('vacancies');

function populateTable(data) {
    const jobContainer = document.getElementById('vacanciesTable');
    jobContainer.innerHTML = '';

    for (const jobId in data) {
        if (data.hasOwnProperty(jobId)) {
            const job = data[jobId];

            // Use backticks to embed the JavaScript function directly
            const jobCard = document.createElement('div');
            jobCard.classList.add('job-card');
            jobCard.innerHTML = `
          <div class="job-info">
            <h3>${job.vacancy_name}</h3>
            <p><strong>Job ID:</strong> ${job.job_id}</p>
            <p><strong>Experience:</strong> ${job.experience} years</p>
            <p><strong>Deadline:</strong> ${job.deadline}</p>
            <p><strong>No Of Openings:</strong> ${job.no_of_vacancies}</p>
          </div>
          <div class="apply-button-container">
            <button class="apply-button" onclick="openForm('${job.job_id}', \`${job.vacancy_name}\`, \`${job.no_of_vacancies}\`, \`${job.job_description}\`)">VIEW DETAILS</button>
          </div>
        `;

            jobContainer.appendChild(jobCard);
        }
    }
}

// Fetch data from Firebase and display all vacancies
function displayVacancies() {
    vacanciesRef.once('value')
        .then(snapshot => {
            const data = snapshot.val();
            populateTable(data);  // Directly populate the table with all data
        })
        .catch(error => console.error('Error fetching vacancies data:', error));
}

// Call the function to display all vacancies when the page loads
displayVacancies();
