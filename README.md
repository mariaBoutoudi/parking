# Parking Module (Drupal 10/11 Portfolio Project)

This Drupal 10/11 custom module serves as a comprehensive portfolio piece, showcasing a wide range of Drupal development techniques and best practices. It implements a fully functional parking management system, demonstrating proficiency in various aspects of custom module development.

## Features

- **Vehicle Check-in:**
  - Custom form to register a vehicle by its license plate and select the type of booking (per hour or per day).
  - A new node of content type 'Parking' is created for each check-in.
  - The node title and 'datetime_in' field are automatically populated with the current timestamp.
- **Vehicle Check-out:**
  - Search form to locate a vehicle using its check-in timestamp (node title).
  - Custom form to finalize check-out for a found vehicle.
  - Displays information about the vehicle (plate and title).
  - Calculates and displays the total parking cost based on the duration as well as the type of booking of stay.
  - Provides a checkout option payment for departure vehicles. Also, there is a functionality for vehicles departuring the parking without payment.
- **Parking Dashboard:**
  - Dedicated dashboard page (`/dashboard`) providing key parking information.
  - Displays available parking spots, total parking capacity, unpaid tickets as well as the cost per hour and per day.
  - Buttons for quick access to check-in and check-out forms.
- **Occupied Spots & Debtors Listing:**
  - View-based page (`/admin/vehicle-list`) displaying a table of nodes of content type "parking".
  - View-based page (`/admin/vehicle-list/debtors`) displaying a table of nodes of content type "parking".
  - Includes sortable columns and exposed filters.

## Technical Highlights (Showcase of Drupal Knowledge)

This module demonstrates a strong command of various Drupal development techniques, including:

- **Custom Services:** Implemented for business logic separation (e.g., parking cost calculation, vehicle management).
- **Dependency Injection:** Extensively used across forms, controllers, and services for loose coupling and testability.
- **Custom Controllers:** Defined to handle page requests for the dashboard and other custom routes, demonstrating clean separation of concerns.
- **Views:** Leveraged for dynamic data listings, specifically for displaying occupied parking spots and debtors, showcasing expertise in data presentation.
- **Configuration Form:** Provided for module-specific settings (e.g., parking spots, hourly and daily rates as well as rate for the first hour).
- **Custom Forms:** Developed for vehicle check-in, check-out search, and check-out processing, showcasing mastery of the Drupal Form API.
- **Custom Node Creation and Update:** Programmatically creating and updating 'Parking' nodes to manage vehicle data.
- **Batch Operations:** Using batch operations to generate dummy nodes.
- **Custom Routes:** Defined for all custom pages and forms, ensuring clean URLs and proper routing.
- **Custom Twig Templates:** Utilized for theming custom forms and pages.

## Installation

1.  Download the module and place it in your Drupal installation's `modules/custom` directory.
2.  Enable the module through the Drupal administration interface (`/admin/modules`).
3.  Ensure the 'Parking' content type is created and configured with the following fields:
    - `field_car_plate` (Text)
    - `field_datetime_in` (Timestamp)
    - `field_datetime_out` (Timestamp, will be populated on checkout )
    - `field_cost` (Number, will be populated on checkout)
    - `field_payment` (Boolean, will be populated on checkout)
    - `field_time_type` (Text)
4.  Verify that the necessary views and forms are automatically created upon module installation. If not, clear your Drupal cache.

## Configuration

After enabling the module, you may need to:

- Either change the parking capacity or the parking rates (hourly rate, daily rate, rate for the first hour). The current cost calculation can be easily modyfied by applying changes in the configuration form (`/admin/config/parking`).

## Usage

- **Check-in a vehicle:** Navigate to `/arrivalform` or use the button on the dashboard.
- **Check-out a vehicle:** Navigate to `/departureform` or use the button on the dashboard. Search for the vehicle using its check-in timestamp.
- **View Dashboard:** Go to `/dashboard`.
- **View Vehicle list & Debtors:** Go to `/admin/vehicle-list` or use the button on the dashboard.

## Module Structure

- `parking.info.yml`: Module information file.
- `parking.module`: Main module file for hooks and general functionality.
- `src/Form/`: Contains custom and configuration form classes (ArrivalForm, DepartuteFormSearch,DepartureFormCheckout, ParkingConfigForm).
- `src/Controller/`: Contains custom controller for the dashboard page as well as the arrival and departure pages.
- `src/Services/`: Contains custom service definitions.
- `config/install/`: Contains default configuration for content types, fields, views, etc. (e.g., `node.type.parking.yml`, `field.storage.node.field_vehicle_plate.yml`, `views.view.parking_occupied_debtors.yml`).
- `templates/`: Custom Twig templates for theming.
- `parking.routing.yml`: Defines module routes.

## Submodule

### generate_vehicles

This submodule extends the parking functionality by providing tools for generating dummy vehicle nodes. This is particularly useful for testing purposes, populating the parking system with sample data, or simulating vehicle activity.

#### Features

- **Vehicle Generation Form:** A dedicated form to generate a specified number of 'Parking' nodes with random titles, vehicle plates, check-in and check-out timestamps, booking time types (hourly or daily). Randomly selected payment (Yes or No) and calculated the cost for each vehicle entity.
- **Drush Command (Optional):** May include a Drush command for command-line generation of vehicles for automated testing or batch operations.

#### Installation

1.  Ensure the main `parking` module is installed and enabled.
2.  Place the `generate_vehicles` submodule in your Drupal installation's `modules/custom/parking/modules` directory, inside the `parking` module.
3.  Enable the submodule through the Drupal administration interface (`/admin/modules`).

#### Usage

- **Generate Vehicles via UI:** Navigate to `/generateform` and use the provided form.
- **Generate Vehicles via Drush (if applicable):** Execute the Drush command from your Drupal root directory (e.g., `drush generate:vehicles [count]`).

## Maintainer

- Maria Boutoudi
- mboutoudi@gmail.com
- [LinkendIn](https://www.linkedin.com/in/maria-boutoudi-261209294/)
- [Drupal Community](https://drupal.org/u/mariab)
