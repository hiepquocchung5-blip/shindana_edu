/Shinedana_edu
├── /assets                 # Publicly accessible assets
│   ├── /css
│   │   └── tailwind.css    # (Or CDN link in head)
│   ├── /js
│   │   └── alpine.js       # (Or CDN link in head)
│   └── /images             # School logos, hero backgrounds
│
├── /config                 # Configuration files
│   ├── db.php              # Database connection
│   └── functions.php       # Helper functions (Auth, Sanitization)
│
├── /admin                  # [SECURE] Admin Portal
│   ├── index.php           # Dashboard (Stats)
│   ├── classes.php         # Class Manager
│   └── finance.php         # Invoice Builder
│
├── /agent                  # [SECURE] Agent Portal
│   ├── index.php           # Dashboard
│   └── register.php        # Student Registration Form
│
├── /auth                   # Authentication Logic
│   ├── login.php           # Login Page (UI)
│   ├── logout.php          # Session Destroyer
│   └── process_login.php   # Backend Login Handler
│
├── index.php               # [PUBLIC] Client Portal (Landing Page)
└── style.css               # Global Styles