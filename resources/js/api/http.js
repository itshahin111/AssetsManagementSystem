import axios from 'axios';

const http = axios.create({
    baseURL: import.meta.env.VITE_API_URL || '/api/v1',
    headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
    },
});

http.interceptors.request.use((config) => {
    const token = localStorage.getItem('asset-system-token');

    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }

    return config;
});

http.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            localStorage.removeItem('asset-system-token');
            localStorage.removeItem('asset-system-user');
            window.dispatchEvent(new CustomEvent('asset-system:unauthenticated'));
        }

        return Promise.reject(error);
    },
);

export default http;
