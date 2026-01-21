import http from 'k6/http';
import { check, sleep } from 'k6';
import { Rate } from 'k6/metrics';

const errorRate = new Rate('errors');

export const options = {
    stages: [
        { duration: '1m', target: 5 },
        { duration: '3m', target: 10 },
        { duration: '3m', target: 20 },
        { duration: '5m', target: 20 },
        { duration: '2m', target: 0 },
    ],
    thresholds: {
        http_req_duration: ['p(95)<1500'],
        http_req_failed: ['rate<0.02'],
    },
};

const BASE_URL = __ENV.BASE_URL || 'http://localhost:8000';
const ADMIN_EMAIL = __ENV.ADMIN_EMAIL || 'admin@example.com';
const ADMIN_PASSWORD = __ENV.ADMIN_PASSWORD || 'password';

let authToken = '';

export function setup() {
    let loginRes = http.post(`${BASE_URL}/login`, {
        email: ADMIN_EMAIL,
        password: ADMIN_PASSWORD,
    });

    if (loginRes.status !== 200) {
        throw new Error('Login fallido');
    }

    return {
        token: loginRes.json('token') || '',
        cookies: loginRes.cookies,
    };
}

export default function (data) {
    const headers = {
        'Accept': 'text/html,application/xhtml+xml',
    };

    let browseRes = http.get(`${BASE_URL}/admin/people`, {
        headers,
    });

    check(browseRes, {
        'browse status 200': (r) => r.status === 200,
        'browse contiene tabla': (r) => r.body.includes('table'),
    }) || errorRate.add(1);

    sleep(1);

    let searchRes = http.get(`${BASE_URL}/admin/people/list?search=Juan`, {
        headers,
    });

    check(searchRes, {
        'search status 200': (r) => r.status === 200,
        'search es JSON': (r) => {
            try {
                JSON.parse(r.body);
                return true;
            } catch {
                return false;
            }
        },
    }) || errorRate.add(1);

    sleep(Math.random() * 2 + 1);
}
