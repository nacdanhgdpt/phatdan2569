const schedule = {
  "25/4 (Thứ Sáu)": {
    "18h - 19h30": [
      {
        title: "Dọn ghế đá chỗ phông 6m",
        leader: "Anh Duy",
        team: "Thanh Thiếu Nam"
      },
      {
        title: "Đem sắt dưới chân cầu thang ra ngoài",
        leader: "Anh Duy",
        team: "Thanh Thiếu Nam"
      }
    ]
  },
  "26/4 (Thứ Bảy)": {
    "9h - 11h30": [
      {
        title: "Bắn khung background 6m x 6m & treo tấm phông",
        leader: "Anh Phi",
        team: "Anh Khoa, Đăng, Nam, Đức Anh + Đoàn Thiếu Nam",
        tools: "2 máy khoan tay"
      },
      {
        title: "Lắp 4 cây sắt + đèn rọi tượng Phật",
        leader: "Anh Khoa",
        team: "Đăng, Nam, Thắng + Thiếu Nam",
        tools: "2 máy khoan tay"
      },
      {
        title: "Soạn khung bàn Phật 3 tầng",
        leader: "Anh Duy",
        team: "Tỉnh, Việt Hoàng, Nam + Thiếu Nam",
        tools: "Khóa vặn bu lông"
      }
    ],
    "13h30 - 17h": [
      {
        title: "Treo phông 6m x 6m",
        leader: "Anh Duy, Anh Tín, Anh Khoa",
        team: "6 bạn Nam Phật tử"
      },
      {
        title: "Lắp ráp khung 3 tầng, bắn gỗ lót sàn bàn Phật",
        leader: "Anh Duy",
        team: "Việt Hoàng, Đức Anh, Nam + Thiếu Nam",
        tools: "2 máy khoan tay"
      }
    ]
  }
};

const tabs = document.getElementById("tabs");
const content = document.getElementById("content");

Object.keys(schedule).forEach((day, index) => {
  const btn = document.createElement("button");
  btn.innerText = day;
  btn.className = index === 0 ? "active" : "";
  btn.onclick = () => renderDay(day, btn);
  tabs.appendChild(btn);
});

function renderDay(day, btn) {
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

// Render ngày đầu tiên
renderDay(Object.keys(schedule)[0], tabs.children[0]);
