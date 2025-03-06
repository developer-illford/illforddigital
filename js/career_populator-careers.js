
const vacanciesRef = firebase.database().ref('vacancies');


function selectDepartment(department) {
    const departmentList = document.querySelectorAll('.department-list li');
    departmentList.forEach(item => item.classList.remove('selected'));

    const selectedDepartment = department || 'All Departments';
    const selectedDepartmentItem = Array.from(departmentList).find(item => item.innerText === selectedDepartment);
    selectedDepartmentItem.classList.add('selected');

    displayVacancies(selectedDepartment);
}


function filterVacanciesByDepartment(data, department) {
    if (department === 'All Departments') {
        return data;
    }

    const filteredData = {};
    for (const jobId in data) {
        if (data.hasOwnProperty(jobId) && data[jobId].job_department === department) {
            filteredData[jobId] = data[jobId];
        }
    }

    return filteredData;
}





function populateTable(data) {
    const jobContainer = document.getElementById('vacanciesTable');
    jobContainer.innerHTML = '';

    for (const jobId in data) {
        if (data.hasOwnProperty(jobId)) {
            const job = data[jobId];

            const jobCard = document.createElement('div');
            jobCard.classList.add('job-card');
            jobCard.innerHTML = `
          <div class="job-info">
            <h3>${job.job_position}</h3>
            <p><strong>Job ID:</strong> ${job.job_id}</p>
          
            <p><strong>Experience:</strong> ${job.job_experience} years</p>
            <p><strong>Deadline:</strong> ${job.job_deadline}</p>
          </div>
          <div class="apply-button-container">
        
<button class="apply-button" onclick="openForm('${job.job_id}', '${job.job_position}')">APPLY NOW</button>

          </div>
        `;

            jobContainer.appendChild(jobCard);
        }
    }
}

// Fetch data from Firebase and display it
function displayVacancies(selectedDepartment) {
    vacanciesRef.once('value')
        .then(snapshot => {
            const data = snapshot.val();
            const filteredData = filterVacanciesByDepartment(data, selectedDepartment);
            populateTable(filteredData);
        })
        .catch(error => console.error('Error fetching vacancies data:', error));
}


selectDepartment(); 
