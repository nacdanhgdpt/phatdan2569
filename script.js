async function fetchScheduleForDay(day) {
  try {
    const fileName = `schedules/${day.replace(/\//g, "-").split(" ")[0]}.csv`; // Convert "25/4 (Thứ Sáu)" to "schedules/25-4.csv"
    const response = await fetch(fileName);

    if (!response.ok) {
      throw new Error(`Failed to fetch ${fileName}: ${response.statusText}`);
    }

    const csvText = await response.text();
    return parseCSV(csvText);
  } catch (error) {
    console.error("Error fetching schedule:", error);
    return null; // Return null if there's an error
  }
}

function parseCSV(csvText) {
  const rows = csvText
    .split("\n")
    .filter(row => row.trim()) // Ignore empty lines
    .map(row => {
      return row.match(/(".*?"|[^",\s]+)(?=\s*,|\s*$)/g)?.map(cell => cell.replace(/^"|"$/g, '').trim()) || [];
    });

  const schedule = {};
  rows.slice(1).forEach(row => {
    if (row.length < 5) return; // Skip rows with insufficient columns
    const [time, title, leader, team, tools] = row;
    if (!schedule[time]) schedule[time] = [];
    schedule[time].push({ title, leader, team, tools: tools || undefined });
  });
  return schedule;
}

async function init() {
  try {
    // Fetch days from JSON file
    const response = await fetch('days.json');
    if (!response.ok) {
      throw new Error('Could not load days data');
    }
    const data = await response.json();
    const days = data.days.map(item => item.display);
    
    const tabs = document.getElementById("tabs");
    const content = document.getElementById("content");

    days.forEach((day, index) => {
      const btn = document.createElement("button");
      btn.innerText = day;
      btn.className = index === 0 ? "active" : "";
      btn.onclick = () => renderDay(day, btn);
      tabs.appendChild(btn);
    });

    // Render ngày đầu tiên
    renderDay(days[0], tabs.children[0]);
  } catch (error) {
    console.error("Error initializing app:", error);
    document.getElementById("content").innerHTML = 
      `<p style="color: red;">Failed to load days data. Please check days.json file.</p>`;
  }
}

async function renderDay(day, btn) {
  // Đổi tab đang chọn
  [...tabs.children].forEach(b => b.classList.remove("active"));
  btn.classList.add("active");

  content.innerHTML = "<p>Loading...</p>"; // Show a loading message
  const schedule = await fetchScheduleForDay(day);

  if (!schedule) {
    content.innerHTML = `<p style="color: red;">Failed to load data for ${day}. Please check the CSV file.</p>`;
    return;
  }

  content.innerHTML = ""; // Clear the loading message
  const dayBlock = document.createElement("div");
  dayBlock.className = "day-block";
  dayBlock.innerHTML = `<h2>📅 ${day}</h2>`;

  Object.entries(schedule).forEach(([time, tasks]) => {
    const timeBlock = document.createElement("div");
    timeBlock.className = "time-block";
    timeBlock.innerHTML = `<h3>⏰ ${time}</h3>`;

    tasks.forEach(task => {
      const div = document.createElement("div");
      div.className = "task";

      // Leader section
      const leaderAvatar = `avatars/${task.leader.replace(/\s+/g, "_")}.jpg`;
      const leaderHTML = `
        <div class="leader">
          <img src="${leaderAvatar}" alt="${task.leader}" onerror="this.src='avatars/default.png'">
          <span>${task.leader} (Leader)</span>
        </div>`;

      // Team members
      const teamMembers = task.team.split(",").map(member => member.trim());
      const teamHTML = teamMembers
        .map(member => {
          const avatarSrc = `avatars/${member.replace(/\s+/g, "_")}.jpg`;
          return `
            <div class="team-member">
              <img src="${avatarSrc}" alt="${member}" onerror="this.src='avatars/default.png'">
              <span>${member}</span>
            </div>`;
        })
        .join("");

      div.innerHTML = `<strong>${task.title}</strong><br>
        ${leaderHTML}
        👥 Nhóm:<div class="team-container">${teamHTML}</div>
        ${task.tools ? `<br>🛠 Dụng cụ: ${task.tools}` : ""}`;
      timeBlock.appendChild(div);
    });

    dayBlock.appendChild(timeBlock);
  });

  content.appendChild(dayBlock);
}

// Initialize the app
init();
