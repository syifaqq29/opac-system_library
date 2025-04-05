// Language translations object
const translations = {
    en: {
      home: "Home",
      guide: "Guide",
      register: "Register",
      facility: "Facility",
      faq: "FAQ",
      createAccount: "Create Account",
      searchPlaceholder: "Search...",
      advancedSearch: "Show Advanced Search",
      loginPortal: "Login Portal",
      selectAccount: "Select your account type",
      adminLogin: "Admin Login",
      forStaff: "For library staff",
      userLogin: "User Login",
      forStudents: "For students & faculty",
      aboutUs: "About Us",
      contactInfo: "Contact Info",
      quickLinks: "Quick Links"
    },
    my: {
      home: "Laman Utama",
      guide: "Panduan",
      register: "Daftar",
      facility: "Kemudahan",
      faq: "Soalan Lazim",
      createAccount: "Cipta Akaun",
      searchPlaceholder: "Cari...",
      advancedSearch: "Tunjukkan Carian Lanjutan",
      loginPortal: "Portal Log Masuk",
      selectAccount: "Pilih jenis akaun anda",
      adminLogin: "Log Masuk Admin",
      forStaff: "Untuk kakitangan perpustakaan",
      userLogin: "Log Masuk Pengguna",
      forStudents: "Untuk pelajar & fakulti",
      aboutUs: "Tentang Kami",
      contactInfo: "Maklumat Hubungan",
      quickLinks: "Pautan Pintas"
    }
  };
  
  function changeLanguage(lang) {
    const elements = document.querySelectorAll('[data-translate]');
    
    elements.forEach(element => {
      const key = element.getAttribute('data-translate');
      if (translations[lang] && translations[lang][key]) {
        if (element.tagName === 'INPUT' && element.getAttribute('placeholder')) {
          element.placeholder = translations[lang][key];
        } else {
          element.textContent = translations[lang][key];
        }
      }
    });
    
    document.documentElement.lang = lang;
    localStorage.setItem('selectedLanguage', lang);
  }
  
  // Language selector toggle
  function toggleLanguageList() {
    const languageList = document.getElementById('languageList');
    languageList.style.display = languageList.style.display === 'none' ? 'block' : 'none';
  }
  
  // Set language with flag
  function setLanguage(langCode, flagUrl) {
    const activeIcon = document.getElementById('activeIcon');
    const activeFlag = document.getElementById('activeFlag');
    const lang = langCode.toLowerCase();
  
    activeIcon.style.display = 'none';
    activeFlag.src = flagUrl;
    activeFlag.style.display = 'block';
    
    changeLanguage(lang);
    toggleLanguageList();
  }
  
  // Initialize on page load
  document.addEventListener('DOMContentLoaded', () => {
    const savedLanguage = localStorage.getItem('selectedLanguage') || 'en';
    const flagUrl = savedLanguage === 'en' ? 
      'https://upload.wikimedia.org/wikipedia/commons/a/a4/Flag_of_the_United_States.svg' : 
      'https://upload.wikimedia.org/wikipedia/commons/6/66/Flag_of_Malaysia.svg';
    
    setLanguage(savedLanguage.toUpperCase(), flagUrl);
  });