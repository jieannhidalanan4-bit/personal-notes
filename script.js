const pages = document.querySelectorAll(".page");
const publicNav = document.getElementById("publicNav");
const logoutButton = document.getElementById("logout");
const registerForm = document.getElementById("registerForm");
const loginForm = document.getElementById("loginForm");
const dialog = document.getElementById("noteDialog");
const noteForm = document.getElementById("noteForm");
const notesGrid = document.getElementById("notesGrid");
const emptyMessage = document.getElementById("emptyMessage");
const searchInput = document.getElementById("search");
const toast = document.getElementById("toast");
let currentUser = JSON.parse(localStorage.getItem("pnsCurrentUser") || "null");
let editingId = null;
let toastTimer;

function users() { return JSON.parse(localStorage.getItem("pnsUsers") || "[]"); }
function notes() { return JSON.parse(localStorage.getItem("pnsNotes") || "[]"); }
function saveNotes(items) { localStorage.setItem("pnsNotes", JSON.stringify(items)); }

function message(text) {
  toast.textContent = text;
  toast.classList.add("show");
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => toast.classList.remove("show"), 2500);
}

function showPage(name) {
  if (["dashboard", "report"].includes(name) && !currentUser) {
    location.hash = "login";
    return;
  }
  pages.forEach(page => page.classList.add("hidden"));
  const page = document.getElementById(name) || document.getElementById("home");
  page.classList.remove("hidden");
  publicNav.classList.toggle("hidden", Boolean(currentUser));
  logoutButton.classList.toggle("hidden", !currentUser);
  document.querySelectorAll("nav a").forEach(link => link.classList.toggle("active", link.getAttribute("href") === "#" + name));
  if (name === "dashboard") {
    document.getElementById("helloUser").textContent = "Hello, " + currentUser.name.split(" ")[0] + "! 👋";
    renderNotes();
  }
  if (name === "report") renderReport();
  window.scrollTo(0, 0);
}

registerForm.addEventListener("submit", event => {
  event.preventDefault();
  const name = document.getElementById("fullName").value.trim();
  const email = document.getElementById("regEmail").value.trim().toLowerCase();
  const password = document.getElementById("regPassword").value;
  const confirm = document.getElementById("confirmPassword").value;
  const allUsers = users();
  if (password !== confirm) return message("Passwords do not match.");
  if (allUsers.some(user => user.email === email)) return message("Email already registered. Please log in.");
  allUsers.push({ name, email, password });
  localStorage.setItem("pnsUsers", JSON.stringify(allUsers));
  registerForm.reset();
  message("Registration successful! Please log in.");
  location.hash = "login";
});

loginForm.addEventListener("submit", event => {
  event.preventDefault();
  const email = document.getElementById("loginEmail").value.trim().toLowerCase();
  const password = document.getElementById("loginPassword").value;
  const user = users().find(item => item.email === email && item.password === password);
  if (!user) return message("Email or password is incorrect. Register first if needed.");
  currentUser = { name: user.name, email: user.email };
  localStorage.setItem("pnsCurrentUser", JSON.stringify(currentUser));
  loginForm.reset();
  message("Login successful!");
  location.hash = "dashboard";
});

logoutButton.addEventListener("click", () => {
  currentUser = null;
  localStorage.removeItem("pnsCurrentUser");
  location.hash = "home";
});

function renderNotes() {
  const mine = notes().filter(note => note.email === currentUser.email);
  const query = searchInput.value.trim().toLowerCase();
  const visible = mine.filter(note => (note.title + " " + note.content).toLowerCase().includes(query));
  notesGrid.innerHTML = "";
  document.getElementById("noteCount").textContent = mine.length + (mine.length === 1 ? " note" : " notes");
  emptyMessage.classList.toggle("hidden", visible.length > 0);
  emptyMessage.querySelector("h3").textContent = mine.length && !visible.length ? "No matching notes" : "No notes yet";
  emptyMessage.querySelector("p").textContent = mine.length && !visible.length ? "Try another search word." : "Click “New Note” to write your first note.";
  visible.forEach(note => {
    const card = document.createElement("article");
    card.className = "note-card " + note.color;
    const title = document.createElement("h3"); title.textContent = note.title;
    const date = document.createElement("p"); date.className = "note-date"; date.textContent = note.date;
    const content = document.createElement("p"); content.className = "note-preview"; content.textContent = note.content;
    const actions = document.createElement("div"); actions.className = "note-actions";
    const edit = document.createElement("button"); edit.className = "small-action"; edit.textContent = "✎ Edit";
    edit.addEventListener("click", () => editNote(note.id));
    const remove = document.createElement("button"); remove.className = "small-action"; remove.textContent = "🗑 Delete";
    remove.addEventListener("click", () => deleteNote(note.id));
    actions.append(edit, remove); card.append(title, date, content, actions); notesGrid.appendChild(card);
  });
}

function openNote(note = null) {
  editingId = note ? note.id : null;
  noteForm.reset();
  document.getElementById("dialogTitle").textContent = note ? "Edit your note" : "Create a note";
  if (note) {
    document.getElementById("noteTitle").value = note.title;
    document.getElementById("noteContent").value = note.content;
    document.getElementById("noteColor").value = note.color;
  }
  dialog.showModal();
}
function editNote(id) {
  const note = notes().find(item => item.id === id && item.email === currentUser.email);
  if (note) openNote(note);
}
function deleteNote(id) {
  if (!confirm("Are you sure you want to delete this note?")) return;
  saveNotes(notes().filter(note => !(note.id === id && note.email === currentUser.email)));
  renderNotes(); message("Note deleted.");
}

document.getElementById("newNote").addEventListener("click", () => openNote());
document.getElementById("addNoteSide").addEventListener("click", () => openNote());
document.getElementById("myNotes").addEventListener("click", () => { searchInput.value = ""; renderNotes(); });
document.getElementById("closeDialog").addEventListener("click", () => dialog.close());
document.getElementById("cancelNote").addEventListener("click", () => dialog.close());
searchInput.addEventListener("input", renderNotes);

noteForm.addEventListener("submit", event => {
  event.preventDefault();
  const title = document.getElementById("noteTitle").value.trim();
  const content = document.getElementById("noteContent").value.trim();
  const color = document.getElementById("noteColor").value;
  const allNotes = notes();
  if (editingId) {
    const note = allNotes.find(item => item.id === editingId && item.email === currentUser.email);
    if (note) Object.assign(note, { title, content, color, date: new Date().toLocaleDateString() });
    message("Note updated!");
  } else {
    allNotes.unshift({ id: Date.now().toString() + Math.random(), email: currentUser.email, title, content, color, date: new Date().toLocaleDateString() });
    message("Note saved!");
  }
  saveNotes(allNotes); dialog.close(); noteForm.reset(); renderNotes();
});

function renderReport() {
  const mine = notes().filter(note => note.email === currentUser.email);
  document.getElementById("reportSummary").textContent = "Prepared for " + currentUser.name + " • " + mine.length + " notes • " + new Date().toLocaleDateString();
  const list = document.getElementById("reportList"); list.innerHTML = "";
  if (!mine.length) {
    const p = document.createElement("p"); p.textContent = "No notes yet. Add a note from your dashboard first."; list.appendChild(p); return;
  }
  mine.forEach((note, index) => {
    const item = document.createElement("article"); item.className = "report-item";
    const title = document.createElement("h3"); title.textContent = (index + 1) + ". " + note.title;
    const date = document.createElement("p"); date.textContent = "Date: " + note.date;
    const content = document.createElement("p"); content.textContent = note.content;
    item.append(title, date, content); list.appendChild(item);
  });
}
document.getElementById("printReport").addEventListener("click", () => window.print());
window.addEventListener("hashchange", () => showPage(location.hash.slice(1) || "home"));
showPage(location.hash.slice(1) || "home");
const fullNameInput = document.getElementById('fullName');

if (fullNameInput) {
    fullNameInput.addEventListener('input', function () {
        this.value = this.value.replace(/[^\p{L}\p{M} '\-]/gu, '');
    });
}
