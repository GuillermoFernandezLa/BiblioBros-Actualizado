// assets/js/main.js – Loads shared components and initializes page-specific logic
document.addEventListener("DOMContentLoaded", () => {
  const components = [
    { id: "navbar-placeholder", file: "navbar.php" },
    { id: "footer-placeholder", file: "footer.php" },
    { id: "modal-container", file: "modals.php" },
  ];

  Promise.all(
    components.map(c =>
      fetch(c.file)
        .then(res => res.ok ? res.text() : "")
        .then(html => {
          let container = document.getElementById(c.id);
          if (!container) {
            container = document.createElement("div");
            container.id = c.id;
            document.body.appendChild(container);
          }
          container.innerHTML = html;
        })
    )
  )
    .then(() => {
      checkSessionAndInit();
      cleanModals();

      // logout wiring
      const logoutBtn = document.getElementById("logout-button");
      const confirmLogoutBtn = document.getElementById("confirmLogout");
      if (logoutBtn && confirmLogoutBtn) {
        logoutBtn.addEventListener("click", e => {
          e.preventDefault();
          new bootstrap.Modal(document.getElementById("logoutModal")).show();
        });
        confirmLogoutBtn.addEventListener("click", () => {
          window.location.href = "logout.php";
        });
      }
    })
    .catch(err => console.error("Component load error:", err));
});

function checkSessionAndInit() {
  fetch("php/session_status.php")
    .then(res => res.json())
    .then(status => {
      const protectedPages = [
        "Topdashboard.php",
        "Topprofile.php",
        "Topchat_mentor.php",
        "Topchat_mentee.php"
      ];
      const onProtected = protectedPages.some(p =>
        window.location.pathname.endsWith(p)
      );
      if (onProtected && !status.authenticated) {
        window.location.href = "Toplogin.php";
        return;
      }

      initLogout(status);
      initDashboard(status);
      initProfile(status);



      if (window.location.pathname.endsWith("Topsubject_mentor.php")) {
        const params = new URLSearchParams(window.location.search);
        const subjectId = params.get("subject_id");

        // 1) Cargar solicitudes desde load_requests.php (NO desde chat_loader_mentor.php)
        fetch(`php/load_requests.php?subject_id=${subjectId}`, { credentials: "include" })
          .then(res => res.ok ? res.json() : Promise.reject(res.status))
          .then(requests => {
            const ulReq = document.getElementById("pending-requests");
            ulReq.innerHTML = "";
            if (!requests.length) {
              ulReq.innerHTML = "<li class='list-group-item'>No pending questions.</li>";
            } else {
              requests.forEach(r => {
                const li = document.createElement("li");
                li.className = "list-group-item d-flex justify-content-between align-items-center flex-wrap";
                li.innerHTML = `
      <div><strong>${r.mentee}</strong> — "${r.message}"</div>
      <form action="php/accept_request.php" method="post" class="mt-2 mt-sm-0">
        <input type="hidden" name="request_id" value="${r.id}">
        <button type="submit" class="btn btn-secondary btn-sm">Accept</button>
      </form>`;
                ulReq.appendChild(li);
              });
            }
          })
          .catch(err => {
            console.error("Error loading requests:", err);
          });

        // 2) Cargar chats activos/cerrados desde chat_loader_mentor.php
        fetch(`php/chat_loader_mentor.php?subject_id=${subjectId}`, { credentials: "include" })
          .then(res => res.ok ? res.json() : Promise.reject(res.status))
          .then(data => {
            const renderChats = (chats, containerId, active) => {
              const ul = document.getElementById(containerId);
              ul.innerHTML = "";
              if (!chats.length) {
                ul.innerHTML = `<li class="list-group-item">No ${active ? 'active' : 'closed'} chats.</li>`;
                return;
              }
              chats.forEach(c => {
                const li = document.createElement("li");
                li.className = "list-group-item d-flex justify-content-between align-items-center";
                const label = active ? 'Open Chat' : 'View History';
                const btnClass = active ? 'btn-primary' : 'btn-secondary';
                const dateLabel = active ? ''
                  : `<span class="text-muted small ms-2">started at ${c.closed_at}</span>`;
                li.innerHTML = `
      Chat with ${c.mentee_name} (${c.subject})
      <a href="Topchat_mentor.php?chat_id=${c.chat_id}" class="btn ${btnClass} btn-sm">${label}</a>
      ${dateLabel}`;
                ul.appendChild(li);
              });
            };

            renderChats(data.active_chats, 'active-chats', true);
            renderChats(data.closed_chats, 'closed-chats', false);
          })
          .catch(err => {
            console.error("Error loading mentor chats:", err);
          });
      }
      if (window.location.pathname.endsWith("Topsubject_mentee.php")) {
        fetch(`php/chat_loader_mentee.php?subject_id=${window.__SUBJECT_ID__}`, { credentials: "include" })
          .then(res => res.ok ? res.json() : Promise.reject(res.status))
          .then(data => {
            // Active chats
            const ulActive = document.getElementById("active-chats");
            ulActive.innerHTML = "";
            if (!data.active_chats.length) {
              ulActive.innerHTML = '<li class="list-group-item">No active chats.</li>';
            } else {
              data.active_chats.forEach(c => {
                const li = document.createElement("li");
                li.className = "list-group-item d-flex justify-content-between align-items-center";
                li.innerHTML = `
      Chat with ${c.mentor_name} (${c.subject})
      <a href="Topchat_mentee.php?chat_id=${c.chat_id}" class="btn btn-primary btn-sm">Open Chat</a>
    `;
                ulActive.appendChild(li);
              });
            }

            // Closed chats
            const ulClosed = document.getElementById("closed-chats");
            ulClosed.innerHTML = "";
            if (!data.closed_chats.length) {
              ulClosed.innerHTML = '<li class="list-group-item">No closed chats.</li>';
            } else {
              data.closed_chats.forEach(c => {
                const li = document.createElement("li");
                li.className = "list-group-item d-flex justify-content-between align-items-center";
                li.innerHTML = `
      Chat with ${c.mentor_name} (${c.subject})
      <a href="Topchat_mentee.php?chat_id=${c.chat_id}" class="btn btn-secondary btn-sm">View History</a>
      <span class="text-muted small ms-2">closed at ${c.closed_at}</span>
    `;
                ulClosed.appendChild(li);
              });
            }
          })
          .catch(err => {
            console.error("Error loading mentee chats:", err);
            document.getElementById("active-chats").innerHTML = '<li class="list-group-item text-danger">Error loading chats.</li>';
            document.getElementById("closed-chats").innerHTML = '<li class="list-group-item text-danger">Error loading chats.</li>';
          });
      }

      // ─────────────────────────────────────────────────
      // CHAT VIEW (both roles)
      // ─────────────────────────────────────────────────
      if (
        ["Topchat_mentee.php", "Topchat_mentor.php"].some(p =>
          window.location.pathname.endsWith(p)
        )
      ) {
        const chatWindow = document.getElementById("chat-window");
        if (chatWindow) chatWindow.style.display = "block";

        const chatId = new URLSearchParams(window.location.search).get("chat_id");
        const chatHistory = document.getElementById("chat-history");
        const chatForm = document.getElementById("chat-form");
        const messageInput = document.getElementById("message-input");

        // Sidebar for Topchat_mentor.php and Topchat_mentee.php
        if (
          ["Topchat_mentee.php", "Topchat_mentor.php"].some(p =>
            window.location.pathname.endsWith(p)
          )
        ) {
          const chatList = document.getElementById("chat-list");
          const currentUserId = Number(chatList.dataset.userId);

          if (isNaN(currentUserId)) {
            console.error("Invalid or missing data-user-id in #chat-list");
            chatList.innerHTML = '<li class="list-group-item text-danger">User not authenticated.</li>';
          } else {
            fetch("php/chat_loader_all.php", { credentials: "include" })
              .then(res => res.ok ? res.json() : Promise.reject(res.status))
              .then(data => {
                chatList.innerHTML = "";
                const allChats = [...data.active_chats, ...data.closed_chats];

                if (!allChats.length) {
                  chatList.innerHTML = '<li class="list-group-item">No chats yet.</li>';
                  return;
                }

                allChats.forEach(c => {
                  const isMentor = Number(c.mentor_id) === currentUserId;
                  const nameToShow = isMentor ? c.mentee_name : c.mentor_name;
                  const redirectTo = isMentor
                    ? `Topchat_mentor.php?chat_id=${c.chat_id}`
                    : `Topchat_mentee.php?chat_id=${c.chat_id}`;

                  const li = document.createElement("li");
                  li.className = "list-group-item list-group-item-action";
                  li.innerHTML = `
            <strong>${nameToShow}</strong>
            <small class="text-muted">(${c.subject})</small>
            ${c.active === "1" ? "" : '<span class="badge bg-secondary ms-2">closed</span>'}
          `;
                  li.addEventListener("click", () => {
                    window.location.href = redirectTo;
                  });
                  chatList.appendChild(li);
                });
              })
              .catch(err => {
                chatList.innerHTML = '<li class="list-group-item text-danger">Error loading chats.</li>';
                console.error("chat_loader_all error:", err);
              });
          }
        }



        // Load chat messages
        function loadMessages() {
          const chatHistory = document.getElementById("chat-history");
          const currentUserId = chatHistory.dataset.userId; // ← mentee user id

          fetch(`php/load_messages.php?chat_id=${chatId}`, { credentials: 'include' })
            .then(res => res.json())
            .then(messages => {
              chatHistory.innerHTML = "";
              messages.forEach(msg => {
                const wrapper = document.createElement("div");

                // Aplica el estilo en base al remitente
                const isMine = msg.sender_id == currentUserId;
                wrapper.className = isMine ? "text-end mb-2" : "text-start mb-2";

                const message = document.createElement("p");
                message.className = "mb-1";
                message.textContent = msg.text;

                const timestamp = document.createElement("small");
                timestamp.className = "d-block text-muted";
                timestamp.textContent = msg.time;

                wrapper.appendChild(timestamp);
                wrapper.appendChild(message);
                chatHistory.appendChild(wrapper);
              });
              chatHistory.scrollTop = chatHistory.scrollHeight;
            })
            .catch(err => {
              console.error("Error loading messages:", err);
              chatHistory.innerHTML = '<p class="text-danger">Failed to load messages.</p>';
            });
        }


        // Handle message sending
        if (chatForm) {
          chatForm.addEventListener("submit", e => {
            e.preventDefault();
            const text = messageInput.value.trim();
            if (!text) return;

            fetch("php/send_message.php", {
              method: "POST",
              credentials: "include",
              headers: { "Content-Type": "application/x-www-form-urlencoded" },
              body: `chat_id=${chatId}&message=${encodeURIComponent(text)}`
            })
              .then(res => {
                if (!res.ok) throw new Error(res.status);
                messageInput.value = "";
                loadMessages(); // Refresh after sending
              })
              .catch(err => console.error("Send error:", err));
          });
        }

        loadMessages();
        setInterval(loadMessages, 5000);
      }



      // ─────────────────────────────────────────────────
      // RATING MODALS WIRING (omitting the old confirm())
      // ─────────────────────────────────────────────────
      if (window.location.pathname.endsWith("rating.php")) {
        const ratingForm = document.getElementById("rating-form");
        const cancelLink = document.querySelector("#rating-form a.btn-secondary");
        const submitModal = new bootstrap.Modal(document.getElementById("submitRatingModal"));
        const cancelModal = new bootstrap.Modal(document.getElementById("cancelRatingModal"));
        const confirmSubmit = document.getElementById("confirmSubmitRating");
        const confirmCancel = document.getElementById("confirmCancelRating");

        // show submit-confirm modal
        ratingForm.addEventListener("submit", e => {
          e.preventDefault();
          submitModal.show();
        });
        confirmSubmit.addEventListener("click", () => {
          submitModal.hide();
          ratingForm.submit();
        });

        // show cancel-confirm modal
        cancelLink.addEventListener("click", e => {
          e.preventDefault();
          cancelModal.show();
        });
        confirmCancel.addEventListener("click", () => {
          window.location.href = cancelLink.getAttribute("href");
        });
      }

    })
    .catch(err => {
      console.error("Error checking session:", err);
      window.location.href = "Toplogin.php";
    });
}

function initLogout(status) {
  // (already wired above)
}

function initDashboard(status) {
  if (window.location.pathname.endsWith("Topdashboard.php") && status.authenticated) {
    const greeting = document.getElementById("user-greeting");
    if (greeting) {
      const firstName = status.fullname.split(" ")[0] || "";
      greeting.textContent = `Welcome back, ${firstName}!`;
    }
    const facultiesCard = document.querySelector('.your-faculties-card');
    const selector = document.querySelector('.faculty-selector');
    if (facultiesCard && selector) {
      facultiesCard.addEventListener('mouseenter', () => selector.classList.remove('visually-hidden'));
      facultiesCard.addEventListener('mouseleave', () => selector.classList.add('visually-hidden'));
    }
    const facultyDropdown = document.getElementById("facultyDropdown");
    if (facultyDropdown) {
      facultyDropdown.addEventListener("change", function () {
        const url = this.value; if (url) window.location.href = url;
      });
    }
  }
}

function initProfile(status) {
  if (!window.location.pathname.endsWith("Topprofile.php") || !status.authenticated) return;
  const params = new URLSearchParams(window.location.search);
  if (params.get("updated") === "1") {
    new bootstrap.Modal(document.getElementById("successModal")).show();
  } else if (params.get("error")) {
    alert("Error: " + decodeURIComponent(params.get("error")));
  }
}

function cleanModals() {
  document.querySelectorAll('.modal').forEach(modal =>
    modal.addEventListener('hidden.bs.modal', () => {
      document.body.classList.remove('modal-open');
      document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
    })
  );
}
