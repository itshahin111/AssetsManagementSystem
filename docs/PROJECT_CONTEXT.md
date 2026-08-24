# PROJECT CONTEXT

## Project
School Asset & Inventory Management System

## Purpose
Production-oriented school asset and inventory management.

## Stack
- Laravel API
- Vue 3
- Vite
- Pinia
- Vue Router
- Tailwind CSS
- Axios
- MySQL 8
- Sanctum
- Spatie Permission

## Current Repository Status
UNKNOWN — inspect actual repository before claiming implementation status.

## Architecture
Layered:
Client -> API -> Application -> Domain/Persistence -> MySQL

Business logic:
Services

Authorization:
Policies + Spatie Permission

Validation:
Form Requests

Responses:
API Resources

Transactions:
Required for transfer/assignment/disposal-style multi-step mutations.

## Current Task
Determine actual repository progress and continue from the next incomplete approved phase.

## Important
The architecture document is a design reference. Actual repository state is authoritative.
