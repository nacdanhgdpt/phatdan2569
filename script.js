async function fetchSchedule() {
  const response = await fetch("schedule.csv");
  const csvText = await response.text();
  return parseCSV(csvText);
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
    if (row.length < 6) return; // Skip rows with insufficient columns
    const [day, time, title, leader, team, tools] = row;
    if (!schedule[day]) schedule[day] = {};
    if (!schedule[day][time]) schedule[day][time] = [];
    schedule[day][time].push({ title, leader, team, tools: tools || undefined });
  });
  return schedule;
}

async function init() {
  const schedule = await fetchSchedule();
  const tabs = document.getElementById("tabs");
  const content = document.getElementById("content");

  Object.keys(schedule).forEach((day, index) => {
    const btn = document.createElement("button");
    btn.innerText = day;
    btn.className = index === 0 ? "active" : "";
    btn.onclick = () => renderDay(day, btn, schedule);
    tabs.appendChild(btn);
  });

  // Render ngày đầu tiên
  renderDay(Object.keys(schedule)[0], tabs.children[0], schedule);
}

function renderDay(day, btn, schedule) {
  // Đổi tab đang chọn
  [...tabs.children].forEach(b => b.classList.remove("active"));
  btn.classList.add("active");

  content.innerHTML = "";
  const dayData = schedule[day];

  const dayBlock = document.createElement("div");
  dayBlock.className = "day-block";
  dayBlock.innerHTML = `<h2>📅 ${day}</h2>`;

  Object.entries(dayData).forEach(([time, tasks]) => {
    const timeBlock = document.createElement("div");
    timeBlock.className = "time-block";
    timeBlock.innerHTML = `<h3>⏰ ${time}</h3>`;

    tasks.forEach(task => {
      const div = document.createElement("div");
      div.className = "task";
      div.innerHTML = `<strong>${task.title}</strong><br>
        👨 Phụ trách: ${task.leader}<br>
        👥 Nhóm: ${task.team}
        ${task.tools ? `<br>🛠 Dụng cụ: ${task.tools}` : ""}`;
      timeBlock.appendChild(div);
    });

    dayBlock.appendChild(timeBlock);
  });

  content.appendChild(dayBlock);
}

// Initialize the app
init();
