# freight-brokering25

## Overview

Welcome to the Freight Brokering25 web application! This platform is designed to streamline freight forwarding and transport consultancy services across Southern Africa, focusing on Zimbabwe and the SADC region. The mission is to facilitate the transportation of goods and assist transport operators in managing their businesses effectively.

## Features

- **Robust Backend Framework**: Built with Laravel
- **Dyanamic User Interactions**: Powered by Livewire.
- **User-Friendly Interface**: Built with Tailwind CSS for a responsive and intuitive user experience.
- **Lightweight and Responsive UI Elements**: Enhanced with AlpineJs.
- **Reliable Data Storage and Retrieval**: Managed by MySQL database.
- **Content Management System (CMS)**: Manage content easily and efficiently.
- **Chat Application**: Real-time communication between users for seamless coordination.
- **Notification System**: Keep users informed about important updates and activities.
- **User Authentication**: Secure login and registration for all users.
- **Authorization Levels**: Different access levels for shippers, carriers, and admins (procurement associate, marketing associate, operations executive, director and system admin) to ensure proper role management.
- **User Profile Management**: Display and edit user profile details such as contact info, passwords etc
- **User Management**: Assign roles, edit, suspend and reset accounts
- **Search Functionality**: Easily find required information
- **Download / Upload Documents**: Reliable and secure file transfer for important documents
- **Automated Testing**: Comprehensive tests to ensure the reliability and functionality of the application.

- **Shipper Dashboard**: 
- Total Shipments: a quick summary of total shipments sent, in transit and delivered.
- Pending Requests: number of shipments requests awaiting approval.
- Available Vehicles Full Details
- Create new shipment: a form to enter details for new shipments (origin, destination, cargo type, weight, pick up date, contact person, budget in full or rate of carriage, etc).
- Download / Upload Documents: Ability to download agreements, invoices, and shipment-related documents as well as upload signed forms.
- Notifications: include alerts for new load assignments, update from brokers and messages regarding shipment status.
- Load Matching: a feature to search for available return loads based on current shipment routes and a button to click preffered carrier.
- Search and Filter Vehicles: List of vehicles by trailer, name, origin, destination, cost, status.

- **Carrier Dashboard**: 
- Total Shipments: a quick summary of loads currently assigned to the carrier, in progress and completed.
- Pending Requests: number of shipments requests awaiting acceptance or action.
- Available Loads Full details: Access posted list of loads that are available for assignment including details like origin, destination, cargo type, quantity, deadlines etc
- Accept or Decline load assignment based on availability and capacity.
- Bid: Optionally make bids with price offers / rate of carriage, to transport the available consignment(s).
- Post Vehicles: Post details of own available vehicles (current location, route, trailer type, carriage rate etc).
- Incident Reports: Fill in a form to immediately report any incidents encountered during transit.
- Download / Upload Documents: Upload necessary documents such as delivery receipts, invoices and compliance documents. Download contracts, agreements and guidelines. 
- Notifications: include alerts for shipment status changes, new messages, requests that need attention, automated reminders for upcoming shipments or renewals.
-  Update shipment tracking: location, status (in transit, delivered) and estimated time of arrival.
- Search and Filter Loads: List of Consignments by category, name, origin, destination, cost, status

- **Marketing Associate Dashboard**: 
- Total Registered Shippers: Display the total number of shippers registered by the associate
- Pending Invoices: Overview of invoices awaiting payment from respective clients.
- Register Shippers: Fill in a form with shipper details.
- Create new Shipment: a form to enter details for new shipments (origin, destination, cargo type, weight, pick up date, contact person, budget in full or rate of carriage, etc) for Shippers that the associate registered.
- Create Invoices: Functionality to generate and send invoices to clients based on contracted services.
- Invoice History: Overview of all invoices issued, including statuses (pending, paid, overdue) for associated shippers.
- Payment Tracking: Track payments received and outstanding balances for each client.
- Mark invoices as paid, when payment is done.
- Feedback Management: Fill in form to collect and track client feedback and satisfaction ratings.
- Download / Upload Documents: Upload necessary documents such as invoices and compliance documents. Download contracts, agreements and guidelines. 
- Notifications: include alerts for invoices that have not yet been paid, messages from clients, delivered consignments
- Shipper Details: Access detailed profiles for each shipper that the asscociate registered, including contact information, service history, and invoice details.

- **Procurement Associate Dashboard**: 
- Total Registered Carriers: Display the total number of carriers registered by the associate
- Incomplete Carrier Registration: number of carriers that the associate registered whose details are not yet complete
- Truck Availability Summary: Overview of available trucks for the carriers registrered by the associate and their statuses.
- Register New Carriers: Form to input details for registering a new carrier, including trade name, directors, trade reference, contacts and company documents.
- View Registered Carriers: List of all carriers registered by the associate with search and filter options.
- Edit Carrier Information: Ability to update details for registered carriers by the procurement associate
- Post Availability of Truck: Post details of available vehicles for carriers registered by the associate (current location, route, trailer type, carriage rate, capacity, available dates etc)
- Download / Upload Documents: Upload necessary documents such as contracts, carrier company profiles, invoices and compliance documents. Download carrier contract forms, load confirmation form, agreements and guidelines. 
- Notifications: include alerts for new invoices received, truck availability updates and messages from carriers.
-  Update shipment tracking: location, status (in transit, delivered) and estimated time of arrival.
- View Incident Reports: View incident reports from carriers on any issues during transit.
- Process Payments: Functionality to mark invoices as processed including options to add notes for the operations and accounts departments.
 

- **Operations Executive Dashboard**: 
- Total Available Loads: Summary of all available loads recieved from the Marketing Associate
- Total Available Trucks: Overview of the trucks provided by the Procurement Associate.
- Pending Invoices: Number of invoices awaiting payment from clients
- Register New Carriers: Form to input details for registering a new carrier, including trade name, directors, trade reference, contacts and company documents.
- View Registered Carriers: List of all carriers registered by the associate with search and filter options.
- Edit Carrier Information: Ability to update details for registered carriers by the associate
- Post Availability of Truck: Post details of  available vehicles (current location, route, trailer type, carriage rate, capacity, available dates etc)
- Download / Upload Documents: Upload necessary documents such as contracts, carrier company profiles, invoices and compliance documents. Download carrier contract forms, load confirmation form, agreements and guidelines. 
- Notifications: include alerts for new invoices received, truck availability updates and messages from carriers.
-  Update shipment tracking: location, status (in transit, delivered) and estimated time of arrival.
- Process Payments: Functionality to mark invoices as processed including options to add notes for the operations and accounts departments.
- NB: Won't access carriers with NO uploaded trucks. 

- **Director Dashboard**
- Total Revenue: Display total revenue generated within a specified period (weekly, monthly, yearly).
- Total Loads Managed: Summary of all loads posted, awarded, and delivered during the reporting period.
- Role and Access Control: Tools to manage user roles, permissions, and access levels for all brokers and associates.
- Carrier Performance: On-time delivery rates, Incident reports or complaints, Average time to complete loads
- Broker Performance: Number of loads managed by each broker, Revenue generated per broker, Client satisfaction ratings
- Financial Metrics: Average time to payment from shippers, Overall operational costs vs. revenue
- Load Status Overview: Visual representation of loads in different stages (e.g., posted, awarded, in transit, delivered) with trends over time.
- Client Revenue Contribution: Breakdown of revenue by client, highlighting top-performing clients and those with overdue payments.
- Client Feedback and Satisfaction: Overview of client satisfaction ratings and feedback trends to identify areas for improvement.
- Invoice Status Dashboard: Summary of all invoices, categorized by status (pending, paid, overdue), with drill-down capabilities for detailed views.
- Payment Trends: Analysis of payment timelines to identify patterns, delays, or issues with specific clients.
- Process Efficiency: Metrics on the time taken for each stage of the load management process, from posting to payment.
- Incident and Resolution Tracking: Overview of incidents reported (e.g., delays, damages) and the time taken to resolve these issues.
- Notifications: include alerts for significant issues, such as high numbers of overdue invoices or performance drops in specific brokers or carriers.
- Scheduled Reports: Options to set up automated reports to be sent via email at regular intervals (e.g., weekly performance summaries).


## Technologies Used

- **Laravel**: PHP framework for building web applications.
- **Livewire Volt**: Framework for building dynamic interfaces without leaving the comfort of Laravel.
- **Alpine Js**: A lightweight Javascript framework for adding interactivity to HTML
- **Tailwind CSS**: Utility-first CSS framework for styling.
- **MySQL**: Database management system.
- **PHPUnit**: For automated testing.
