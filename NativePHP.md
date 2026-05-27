You are a senior software architect and desktop application engineer.

I have an existing Laravel-based supermarket/accounting/inventory management system that was originally built as a web application.

The system is now being transformed into a true desktop application using:

* Laravel 13
* NativePHP
* Electron
* PHP
* Node.js 22
* SQLite (primary local database)
* Offline-first architecture

Your task is to help me fully transform this system from a browser-first web app into a professional desktop business application.

This is NOT a simple Electron wrapper around a website.

The application must behave like a real native desktop software similar to:

* POS systems
* Accounting desktop software
* Inventory management software
* School management desktop apps
* Offline enterprise desktop tools

==================================================
PRIMARY GOALS
=============

1. Remove browser assumptions completely
2. Optimize everything for desktop usage
3. Make the application work fully offline
4. Use SQLite properly for local database storage
5. Build installable executables (.exe, AppImage, etc.)
6. Redesign the UI/UX for desktop workflows
7. Improve keyboard-driven operations
8. Support local file system operations
9. Support Excel import/export heavily
10. Improve speed and responsiveness
11. Support receipt printing and local printers
12. Prepare architecture for future auto-updates
13. Maintain Laravel as backend logic engine
14. Optimize for low-resource business computers

==================================================
IMPORTANT CONTEXT
=================

The current application already contains:

* Inventory management
* Sales/POS
* Accounting features
* Receipt generation
* Excel import/export
* User authentication
* Reporting
* Dashboard
* Product management
* Supplier management
* Customer management

The current app was originally:

* Docker-based locally
* Browser-oriented
* Web-navigation based

Now the system must become:

* Desktop-first
* Offline-first
* SQLite-based
* Electron-native feeling
* Multi-window capable
* Keyboard optimized
* Native file access capable

==================================================
ARCHITECTURAL REQUIREMENTS
==========================

I want a complete migration plan from:
Web Architecture → Desktop Architecture

The new architecture should include:

1. Local SQLite strategy
2. File storage strategy
3. Desktop session persistence
4. Offline-first data architecture
5. Local backups
6. Database migration strategy
7. Local configuration management
8. Printer integration
9. Excel workflow integration
10. Native filesystem integration
11. Desktop notifications
12. Secure local storage
13. App packaging/build system
14. Auto-update preparation
15. Logging/crash reporting
16. Electron security best practices
17. NativePHP best practices
18. State management redesign
19. Performance optimization
20. Background task handling

==================================================
DESKTOP UX REQUIREMENTS
=======================

The system should stop behaving like a website.

I want recommendations and implementation steps for:

1. Desktop-style sidebar navigation
2. Keyboard shortcuts everywhere
3. Fast cashier workflow
4. Persistent desktop window state
5. Modal-driven workflows
6. Multi-panel layouts
7. Better data tables
8. Desktop notifications
9. Native dialogs
10. Loading optimization
11. Large-screen optimization
12. Responsive desktop layouts
13. Reduced browser-like behavior
14. Native-feeling interactions
15. Improved accessibility
16. Fullscreen workflows where needed
17. POS-focused UX
18. Reduced page reload mentality
19. Better desktop typography and spacing
20. Smooth desktop transitions

==================================================
DATABASE REQUIREMENTS
=====================

I want to migrate properly to SQLite.

Please provide:

* Best SQLite structure for Laravel desktop apps
* WAL mode optimization
* Concurrency handling
* Local database path strategy
* Backup/export system
* Corruption prevention
* Data migration strategy from MySQL
* Performance optimization
* Local encryption recommendations
* Offline-safe transaction handling

==================================================
FILE SYSTEM REQUIREMENTS
========================

The application must support:

* Excel import/export
* Local backups
* Receipt export
* PDF export
* CSV import/export
* File browsing
* Drag-and-drop imports
* Local storage directories
* Image storage
* Automatic backup folders

==================================================
EXECUTABLE REQUIREMENTS
=======================

I want production-ready builds for:

* Windows .exe
* Linux AppImage
* Optional macOS later

Provide:

* Build pipeline
* Packaging setup
* NativePHP build optimization
* Electron optimization
* App icon setup
* Installer strategy
* Code signing recommendations
* Update strategy
* Build automation
* Release management

==================================================
DEVELOPMENT REQUIREMENTS
========================

I want this project approached like a senior engineering team.

Your response must:

* Be extremely concrete
* Be implementation-focused
* Be step-by-step
* Be production-minded
* Avoid generic advice
* Include folder structure recommendations
* Include architectural decisions
* Include priorities/phases
* Include migration sequencing
* Include risk management
* Include desktop-specific considerations

==================================================
OUTPUT FORMAT REQUIRED
======================

Structure your response in this order:

1. Desktop Transformation Overview
2. Architecture Redesign
3. SQLite Migration Plan
4. NativePHP + Electron Structure
5. UI/UX Desktop Redesign
6. Navigation Refactor
7. Offline-First Strategy
8. File System Integration
9. Excel Workflow Design
10. Printing System Design
11. Performance Optimization
12. Security Hardening
13. Packaging & Build Strategy
14. Recommended Folder Structure
15. Step-by-Step Implementation Phases
16. Risk Areas & Solutions
17. Production Readiness Checklist

For each section:

* Explain WHY
* Explain HOW
* Explain implementation details
* Give recommended packages/tools
* Give Laravel-specific guidance
* Give NativePHP-specific guidance

Act like a principal engineer designing a professional desktop business software platform.
