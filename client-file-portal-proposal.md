# Proposal for Client File Storage and Delivery Portal

> Historical proposal document only.
> The live system truth is documented in `AGENTS.md` and `docs/*`.
> Keep this file for original planning context, not current implementation guidance.

## Prepared For
Internal Production Team, Agents, and Clients

## Project Title
Client File Storage and Delivery Portal

## Executive Summary
This proposal presents the development of a secure, responsive web-based file storage and delivery portal designed for a production-driven workflow. The system will allow the production team to upload and manage photo assets, enable agents to access all client folders, and allow clients to securely view, preview, search, and download only the files assigned to them.

## Live System Delta
The current implemented portal differs from some early proposal assumptions:

- registration creates a `client` account immediately
- the first client request creates the assigned folder
- assignment chat is now part of the live workflow
- agents browse and download only the files allowed by backend authorization
- admin and production ownership is split across assignment, due dates, execution, and uploads

The proposed platform addresses the need for a centralized and controlled way to distribute raw photo materials without relying on scattered manual sharing methods. Unlike generic file-sharing tools, this portal will be structured around role-based access, client-specific folders, approval-based onboarding, and operational oversight for the production team.

## Background of the Project
At present, file delivery processes are often handled through ad hoc sharing methods that can become disorganized, difficult to track, and less secure as the volume of clients and assets increases. A dedicated portal tailored to the organization’s workflow will improve file accessibility, reinforce access control, and create a cleaner user experience for clients and internal users.

The project is intended first for internal company use with client-facing access. It focuses on fast MVP delivery, short iteration cycles, and a clean architecture that can support future enhancements such as notifications, broader activity logging, and richer file management capabilities.

## Problem Statement
The current process for storing and delivering requested photo materials lacks a centralized and structured system. This creates several operational concerns:

- Difficulty keeping client files organized in one controlled location
- Limited visibility into who has access to which files
- Inefficient manual file sharing for agents and clients
- Risk of clients accessing files not intended for them
- Lack of a recovery process for accidentally deleted files
- Inconsistent client experience when receiving materials

## Proposed Solution
The proposed solution is a dedicated file storage and delivery portal with role-based access control and client-based folder separation. The production team will manage uploads and file organization, agents will be able to access all client folders for operational use, and clients will only be able to access their own approved folder.

This system will include:

- Client self-registration with manual approval by the production team
- One client account mapped to one client folder
- Production-managed uploads only
- Agent access across all client folders
- Client dashboard with recent files and folder access
- Agent workspace with searchable folders and recent updates
- Production admin area for approvals, file management, recycle bin, and logs
- Recycle bin with 30-day retention before permanent deletion

## Project Objectives
- Centralize the storage and delivery of client photo files
- Improve the client experience through a premium, responsive portal
- Reduce dependency on manual or scattered file-sharing processes
- Enforce role-based file access and client-specific restrictions
- Provide the production team with better operational control
- Establish a scalable technical foundation for future enhancements

## Scope of Work
### Included in Scope
- Responsive web application for desktop and mobile use
- Separated `backend` and `frontend` applications
- Client, agent, and production user roles
- Client self-registration and production approval workflow
- Secure authentication with email and password
- Client-specific folder assignment
- Photo upload, preview, search, and download features
- Production-only file upload and management
- Activity logging for upload and delete actions
- Recycle bin with restore and automatic purge logic

### Excluded from Scope for Version 1
- Public share links
- Notifications and email-based file alerts
- Client or agent file uploads
- Advanced metadata tagging and analytics
- Complex request workflows and approval chains
- Multiple client accounts assigned to one folder

## Functional Requirements
### Production Team
- Create and manage agent accounts
- Review and approve or reject client registrations
- Create and manage client folders
- Upload, rename, move, delete, and restore files
- Access recycle bin and activity logs
- View all client and agent-accessible materials

### Agents
- Log in and access all client folders
- Search file and folder names
- Preview photo files in the browser
- Download files across client folders
- View recently updated folders and recent activity summaries

### Clients
- Self-register for an account
- Log in after approval
- Access only their own assigned folder
- View a dashboard with recent files
- Search, preview, and download their files

## Non-Functional Requirements
- Responsive interface for desktop and mobile
- Secure authenticated access for all downloads
- Clear and user-friendly navigation
- Structured and maintainable backend/frontend architecture
- Scalable file storage using cloud object storage
- Cost-effective MVP-oriented implementation

## Proposed System Architecture
The project will use a separated application architecture:

- `backend`: Laravel API responsible for authentication, authorization, business logic, file/folder metadata, approvals, activity logs, recycle-bin behavior, and cloud storage integration
- `frontend`: Vue 3 application responsible for the user interface, dashboards, search, previews, and operational workflows

The two applications will communicate using `Axios` over authenticated API requests.

### Technology Stack
- Frontend: Vue 3
- Styling: Tailwind CSS
- HTTP Client: Axios
- Backend: Laravel
- Authentication: Laravel Sanctum
- Database: MySQL or MariaDB
- File Storage: Cloud object storage such as Amazon S3 or Cloudflare R2
- Version Control and CI: GitHub and GitHub Actions

## File and Access Structure
The system will organize content through client-based folder separation.

- Each client will have one dedicated top-level folder
- Each client account will be assigned to one client folder
- Agents can access all client folders
- Production users can access and manage everything
- Clients can only access their own folder and its files

## Development Approach
The project will be delivered using short sprint cycles to support quick iteration and frequent validation. This is especially important because the first version is intended as an MVP and may be developed by a small team or even a solo developer.

The development approach will emphasize:

- Fast delivery of core business value
- Clear modular separation between backend and frontend
- Simple but scalable structure for future enhancements
- Continuous validation of role-based security and usability

## Project Milestones and Estimated Timeline
| Task | Estimated Start Date | Estimated End Date |
|---|---|---|
| Project setup, architecture, schema, and UI planning | 20 Apr. 2026 | 01 May 2026 |
| Authentication, registration, approvals, and role permissions | 04 May 2026 | 15 May 2026 |
| Client folders, file upload backend, and cloud storage integration | 18 May 2026 | 29 May 2026 |
| Client dashboard, preview, search, and download workflows | 01 Jun. 2026 | 12 Jun. 2026 |
| Agent workspace and cross-client browsing capabilities | 15 Jun. 2026 | 26 Jun. 2026 |
| Production admin tools, recycle bin, restore flow, and activity logs | 29 Jun. 2026 | 10 Jul. 2026 |
| Responsive polish, testing, security validation, and bug fixing | 13 Jul. 2026 | 24 Jul. 2026 |
| Final QA, deployment preparation, and MVP release | 27 Jul. 2026 | 07 Aug. 2026 |

## Benefits of the Proposed System
- Cleaner and more professional file delivery process
- Stronger access control for sensitive client materials
- Better internal operational efficiency
- Easier navigation for agents and clients
- Reduced risk of file loss through recycle-bin recovery
- Improved client experience through a branded, structured portal
- Scalable foundation for future upgrades

## Risks and Mitigation
### Potential Risks
- Delays caused by infrastructure or storage configuration
- Role/permission edge cases creating unintended access
- Growth in scope during MVP development
- Storage cost increases as asset volume grows

### Mitigation Strategies
- Keep version 1 focused on core file storage and delivery only
- Validate permissions through route, API, and UI testing
- Use modular architecture to keep future changes manageable
- Start with essential activity logging and simple search only

## Success Criteria
The project will be considered successful for the MVP if it achieves the following:

- Clients can register, be approved, and access only their own files
- Agents can browse and download across client folders
- The production team can fully manage folders and uploads
- File preview and download work reliably in the browser
- Deleted files can be restored within the 30-day recycle-bin period
- The system delivers a better client experience than current manual methods

## Conclusion
This project offers a practical and scalable solution for managing and delivering client photo materials in a more secure, organized, and professional way. By separating the platform into a Laravel backend and a Vue frontend, the system remains maintainable, responsive, and ready for future growth.

The proposed MVP focuses on the most important operational needs first: centralized storage, controlled access, easy downloads, and a better client experience. With a short-sprint development approach, this portal can be delivered efficiently while leaving room for future enhancements as the workflow matures.
