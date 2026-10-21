<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Student Portal') - Grievance Redressal System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --primary-light: #eef2ff;
            --surface: #ffffff;
            --background: #f8fafc;
            --border: #e2e8f0;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --danger: #ef4444;
            --danger-light: #fef2f2;
            --success: #10b981;
            --success-light: #ecfdf5;
            --radius: 12px;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: var(--background);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            line-height: 1.5;
        }

        header {
            background-color: var(--surface);
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .navbar {
            max-width: 1100px;
            margin: 0 auto;
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .brand {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--text-main);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .brand-badge {
            background: var(--primary-light);
            color: var(--primary);
            font-size: 0.75rem;
            padding: 0.2rem 0.6rem;
            border-radius: 9999px;
            font-weight: 600;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .nav-link {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.925rem;
            font-weight: 500;
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .nav-link:hover {
            color: var(--primary);
            background-color: var(--primary-light);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.55rem 1.1rem;
            font-size: 0.925rem;
            font-weight: 600;
            border-radius: 8px;
            border: 1px solid transparent;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-primary {
            background-color: var(--primary);
            color: #ffffff;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
        }

        .btn-outline {
            border-color: var(--border);
            color: var(--text-main);
            background-color: var(--surface);
        }

        .btn-outline:hover {
            background-color: var(--background);
            border-color: #cbd5e1;
        }

        .btn-danger {
            background-color: var(--danger-light);
            color: var(--danger);
            border-color: #fecaca;
        }

        .btn-danger:hover {
            background-color: #fee2e2;
        }

        main {
            flex: 1;
            max-width: 1100px;
            width: 100%;
            margin: 0 auto;
            padding: 2rem 1.5rem;
        }

        .auth-container {
            max-width: 440px;
            margin: 2.5rem auto;
            background: var(--surface);
            padding: 2rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
        }

        .auth-header {
            margin-bottom: 1.75rem;
            text-align: center;
        }

        .auth-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 0.4rem;
        }

        .auth-header p {
            color: var(--text-muted);
            font-size: 0.925rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 0.4rem;
        }

        .form-control {
            width: 100%;
            padding: 0.65rem 0.85rem;
            font-size: 0.95rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text-main);
            background-color: #ffffff;
            transition: border-color 0.15s, box-shadow 0.15s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
        }

        .form-control.is-invalid {
            border-color: var(--danger);
        }

        .invalid-feedback {
            color: var(--danger);
            font-size: 0.825rem;
            margin-top: 0.35rem;
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-check-input {
            width: 1rem;
            height: 1rem;
            accent-color: var(--primary);
            cursor: pointer;
        }

        .form-check-label {
            font-size: 0.875rem;
            color: var(--text-muted);
            cursor: pointer;
        }

        .alert {
            padding: 0.9rem 1.1rem;
            border-radius: 8px;
            font-size: 0.925rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert-success {
            background-color: var(--success-light);
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-danger {
            background-color: var(--danger-light);
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .staff-notifications {
            position: fixed;
            top: 5.5rem;
            right: 1.5rem;
            z-index: 60;
            width: min(380px, calc(100% - 3rem));
        }

        .staff-notification {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            padding: 1rem;
            margin-bottom: 0.75rem;
            color: var(--text-main);
            background: var(--surface);
            border: 1px solid var(--primary);
            border-left: 4px solid var(--primary);
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
        }

        .staff-notification-close {
            align-self: flex-start;
            padding: 0;
            color: var(--text-muted);
            background: transparent;
            border: 0;
            font-size: 1.35rem;
            line-height: 1;
            cursor: pointer;
        }

        .auth-footer {
            margin-top: 1.5rem;
            text-align: center;
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .auth-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }

        footer {
            border-top: 1px solid var(--border);
            padding: 1.5rem;
            text-align: center;
            font-size: 0.85rem;
            color: var(--text-muted);
            background-color: var(--surface);
        }

        .staff-shell {
            max-width: 1180px;
        }

        .staff-kicker {
            color: var(--primary);
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .staff-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
        }

        .staff-stat {
            padding: 1.15rem;
        }

        .staff-stat-label {
            color: var(--text-muted);
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .staff-stat-value {
            font-size: 1.9rem;
            font-weight: 700;
            margin-top: 0.35rem;
        }

        .status-badge, .priority-badge, .verification-badge {
            border: 1px solid;
            border-radius: 9999px;
            display: inline-flex;
            font-size: 0.72rem;
            font-weight: 700;
            padding: 0.22rem 0.6rem;
            text-transform: capitalize;
            white-space: nowrap;
        }

        .status-pending { background: #fff7ed; border-color: #fed7aa; color: #c2410c; }
        .status-in_progress { background: #eff6ff; border-color: #bfdbfe; color: #1d4ed8; }
        .status-resolved { background: #f0fdf4; border-color: #bbf7d0; color: #15803d; }
        .status-rejected { background: #fef2f2; border-color: #fecaca; color: #b91c1c; }
        .priority-low { background: #f0fdf4; border-color: #bbf7d0; color: #15803d; }
        .priority-medium { background: #fffbeb; border-color: #fde68a; color: #b45309; }
        .priority-high { background: #fef2f2; border-color: #fecaca; color: #b91c1c; }
        .verification-badge { background: #ecfdf5; border-color: #a7f3d0; color: #047857; }

        @media (max-width: 700px) {
            .staff-filters, .staff-detail-grid {
                grid-template-columns: 1fr !important;
            }
        }

        @media (max-width: 640px) {
            .navbar {
                padding: 0.85rem 1rem;
                align-items: flex-start;
                gap: 0.75rem;
            }

            .brand {
                font-size: 1rem;
                line-height: 1.25;
            }

            .brand-badge {
                display: none;
            }

            .nav-links {
                justify-content: flex-end;
                gap: 0.25rem;
                flex-wrap: wrap;
            }

            .nav-link {
                font-size: 0.8rem;
                padding: 0.35rem 0.45rem;
            }

            main {
                padding: 1.25rem 1rem;
            }

            .mobile-stack {
                flex-direction: column;
                align-items: stretch !important;
            }

            .mobile-full-width {
                width: 100%;
            }

            .mobile-single-column {
                grid-template-columns: 1fr !important;
            }

            .mobile-card-padding {
                padding: 1rem !important;
            }

            .mobile-heading {
                font-size: 1.4rem !important;
                overflow-wrap: anywhere;
            }

            .mobile-complaint-row {
                align-items: flex-start !important;
                flex-direction: column;
                padding: 1rem !important;
            }

            .mobile-badges {
                width: 100%;
                flex-wrap: wrap;
            }

            footer {
                padding: 1.25rem 1rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav class="navbar">
            <a href="{{ auth()->check() && auth()->user()->isStaff() ? route('staff.dashboard') : route('dashboard') }}" class="brand">
                <span>Grievance Redressal</span>
                <span class="brand-badge">{{ auth()->check() && auth()->user()->isStaff() ? 'Staff' : 'Student' }}</span>
            </a>
            <div class="nav-links">
                @auth
                    @if(auth()->user()->isStaff())
                        <a href="{{ route('staff.dashboard') }}" class="nav-link">Staff Dashboard</a>
                    @else
                        <a href="{{ route('dashboard') }}" class="nav-link" id="nav-dashboard">Dashboard</a>
                        <a href="{{ route('complaints.index') }}" class="nav-link" id="nav-complaints">My Complaints</a>
                        <a href="{{ route('complaints.create') }}" class="nav-link" id="nav-new-complaint">+ New</a>
                    @endif
                    <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-outline" style="padding: 0.4rem 0.8rem; font-size: 0.85rem;" id="btn-logout">Log Out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="nav-link">Log In</a>
                    <a href="{{ route('register') }}" class="btn btn-primary" style="padding: 0.4rem 0.9rem; font-size: 0.85rem;">Register</a>
                @endauth
            </div>
        </nav>
    </header>

    <main>
        @if(session('success'))
            <div class="alert alert-success">
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @yield('content')
    </main>

    <footer>
        <p>&copy; {{ date('Y') }} Grievance Redressal System &bull; Student Portal</p>
    </footer>
    <script>
        document.querySelectorAll('[data-dismiss-notification]').forEach(function (button) {
            button.addEventListener('click', function () {
                button.closest('[data-notification]').remove();
            });
        });
    </script>
</body>
</html>
