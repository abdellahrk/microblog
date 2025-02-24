## A microblog for the public.

This is a simple Blogging platform that makes use of so many Symfony features.
It can be used with any front-end framework or mobile app.

Api documentation is found at `base_url/api/doc`

### Admin User
Only admin users can access the admin dashboard at `base_url/admin`

##### Add admin user
Many admin users can be add in the system with the following command
```bash
bin/console app:add-admin-user
```

A simple Flutter app at [microblog mobile app](https://github.com/abdellahrk/microblog-mobile-app) will be consuming this backend 

### Features 
 - [x] Registration 
 - [x] Token Login 
 - [x] Refresh Token
 - [x] Add Blog Post
 - [x] Edit Blog Post
 - [x] Get Single Blog Post with Comments [maybe with pagination]
 - [x] Get Paginated Blog Posts
 - [x] Delete Blog Post
 - [ ] Update User Profile
 - [x] List User Posted Blog Posts
 - [x] List All Blog Posts [Paginated]
 - [ ] Delete User Account
 - [ ] Like Blog Post
 - [x] Email Notifications [WIP]
   - [x] Welcome Email
   - [ ] Activity Notification such as following an author, blog post like, comment added and more
   - [ ] Reminders like completing drafts, verifying accounts and more
 - [x] Add Comments
 - [x] Queuing system with Symfony Messenger
 - [x] Admin Panel
 - [x] Api Documentation