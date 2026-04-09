Codex Prompt — Phase 1: Laravel + Filament + Docker Foundation

You are my senior Laravel architect and implementation agent.

I am building a supermarket inventory and daily sales management system.
This phase is only for the foundation setup, not the business modules yet.

Tech Stack
Laravel
Filament
MySQL
Docker / Docker Compose
PHP 8.3+
Node/Vite
Tailwind (default Laravel/Filament setup is fine)
Main Goal for This Phase

Set up a clean, scalable, production-minded Laravel + Filament project with Docker, database, authentication, authorization foundation, seeders, initial user bootstrap, and developer-friendly environment.

This project will mostly be operated through the Filament admin panel.

Important Context

For now, the system will only have 2 user levels:

sudo
this is the developer/super user
full unrestricted access
can create the first admin user from within the system later
admin
this is the supermarket owner
operational owner access
for now, no other roles are needed

At first launch, the system should automatically create the sudo user through seeding/bootstrap.

Initial sudo user credentials
email: akinjoseph221@gmail.com
password: akinjoseph221@gmail.com

Use that exactly for local development bootstrap.

However:

clearly mark this as development bootstrap only
structure the code so production can later replace this with environment-driven credentials or manual secure onboarding
High-Level Expectations

I want the foundation to be:

clean
modular
scalable
secure
fast
easy to maintain
user-friendly
production-minded

Do not over-engineer, but do not do lazy setup either.

What You Must Do
1. Initialize and Configure the Laravel Project

Set up the Laravel project properly and configure it for Docker-based local development.

Requirements
use best-practice Laravel folder organization
keep the app ready for future scaling
keep configuration environment-driven
ensure .env.example is accurate
ensure the project can be started with Docker only
2. Dockerize the Project Properly

Create a clean Docker development setup.

Requirements

Set up:

app container for PHP/Laravel
nginx container
mysql container
optional node workflow if necessary, but keep dev simple
volumes for local development
proper port exposure
healthy container communication
Deliverables

Create and configure:

docker-compose.yml
Dockerfile
docker/nginx/default.conf or equivalent
any startup/entrypoint scripts if needed
Expectations
app should boot reliably
database should connect cleanly
migrations should run properly
containers should be named clearly
config should be readable and maintainable
3. Install and Configure Filament

Install Filament admin panel properly.

Requirements
configure Filament for this app
use Filament as the primary admin interface
make sure login works
prepare it for future widgets, resources, and custom pages
keep naming clean and conventional
Expectations
Filament panel should load successfully
authentication should be functional
future resources should be easy to add
4. Set Up Authentication and Authorization Foundation

Implement authentication and role handling properly.

Requirements
use Laravel best practices
use policies / gates / middleware foundation where appropriate
do not hardcode authorization logic everywhere
prepare a clean role-based structure for future expansion
Current roles
sudo
admin
Expectations
roles should be stored in the database cleanly
role checks should be easy to use in Filament and backend logic
sudo should have unrestricted access
admin should be supported as a separate role from day one
future roles should be easy to add later without refactoring everything
Recommendation

Use a robust role/permission structure.
If using a package, use a widely accepted Laravel approach and wire it cleanly.
If not using a package, implement a minimal but scalable internal role system.

5. Automatically Bootstrap the First Sudo User

I want the first sudo user to be created automatically after initial setup.

Requirements
create database seeder(s) for bootstrap
create sudo user automatically
assign sudo role automatically
make seeding idempotent where practical
do not create duplicates on repeated seeding
Sudo credentials
email: akinjoseph221@gmail.com
password: akinjoseph221@gmail.com
Important

This is for initial dev bootstrap.
Add a note in code comments and README that this must be changed before production.

6. Prepare the System So Sudo Can Create Admin Later

For this phase, I do not want the admin user created automatically unless you think it is necessary for testing.

Instead:

sudo should exist automatically
structure user management so sudo can later create admin from inside the panel
if a user management resource is needed now, scaffold it cleanly but keep it minimal
7. Database Setup

Set up the database cleanly and prepare the necessary core tables.

Must include
users table
roles / permissions foundation
any required pivot tables
standard Laravel supporting tables as needed
migrations that are clean and future-friendly
Expectations
migration order should be correct
database naming should be consistent
foreign keys should be properly defined
indexes should be added where sensible
no sloppy schema design
8. Middleware and Security Foundation

I want proper security and middleware care from the beginning.

Requirements

Ensure good foundation for:

authentication
authorization
route protection
role-based access control
secure password hashing
session security
CSRF protection
validation conventions
production-safe error handling
least-privilege thinking
Expectations
do not expose debug-sensitive behavior in production config
structure middleware cleanly
secure defaults
comments only where useful, not noisy
9. Performance and Scalability Foundation

Even though this is just phase 1, I want the project ready for growth.

Requirements

Set up the codebase so it can scale later.

Be mindful of:

clean service structure
config separation
avoiding logic buried inside random places
future support for dashboard widgets and custom pages
future import/export workflows
future inventory and transaction modules
future weekly and daily reporting
Expectations
no monolithic controller-style mess
no fragile setup
keep the project easy to extend
10. Developer Experience

Make the project easy to run and easy to understand.

Requirements

Provide:

concise README
setup instructions
Docker commands
migration/seeding commands
how to access Filament
default local URLs
any known caveats
Expectations
another developer should be able to clone and run this without confusion
Implementation Style Rules

Follow these strictly:

Work in atomic steps
Keep commits/tasks logically separated
Explain what files you create or change
Do not skip important setup details
Prefer maintainability over shortcuts
Prefer clarity over cleverness
Use Laravel conventions
Use secure defaults
Avoid unnecessary complexity
Keep future supermarket modules in mind
Expected Output Format

Work step by step.

For each step:

state the goal
make the changes
show the important files created/updated
explain why
mention commands to run
mention how to verify it works

At the end, provide:

A. File tree summary

Show major relevant files and folders added/changed.

B. Setup commands

List exact commands to run.

C. Verification checklist

Include checks for:

Docker boots
app loads
database connects
migrations succeed
seeders succeed
sudo user exists
Filament login works
D. Notes / next step recommendations

Suggest the next implementation phase after this foundation is complete.

Acceptance Criteria

This phase is complete only if all of these are true:

Laravel runs inside Docker successfully
MySQL runs inside Docker successfully
App connects to MySQL successfully
Migrations run without errors
Filament installs and loads correctly
Filament authentication works
Role system is in place with sudo and admin
Seeder creates the sudo user automatically
Sudo user can log into Filament
Authorization foundation is structured for future growth
README explains local setup clearly
Codebase is clean and ready for the next module phase
Technical Preferences

Use sensible defaults, but prefer these decisions where appropriate:

Laravel latest stable version
PHP 8.3+
MySQL 8+
Filament latest stable compatible version
use database seeders and factories properly
use environment variables for config
use named Docker services
use non-root-friendly patterns where sensible
use strict validation and clean migration structure
Constraints
Do not implement the supermarket inventory business logic yet
Do not build product, category, stock, or sales modules yet
Do not overbuild dashboard widgets yet
Focus only on the foundation needed to support those later
Extra Note

This system will later include:

categories
products
stock entries
daily sales Excel export/import
dashboard widgets
custom Filament pages
low-stock monitoring
reporting

So make architectural decisions with those future modules in mind.

Final Instruction

Now implement this foundation in a careful, production-minded, developer-friendly way.
Proceed step by step, and do not skip setup quality.

Add this small note for yourself

You may want to change this line in the prompt before using it:

password: akinjoseph221@gmail.com

That will work for bootstrap, but it is weak for anything beyond local/dev.