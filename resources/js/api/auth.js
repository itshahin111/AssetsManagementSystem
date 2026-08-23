import http from './http';

export const authApi = {
    login: (payload) => http.post('/auth/login', payload).then((response) => response.data),
    logout: () => http.post('/auth/logout').then((response) => response.data),
    me: () => http.get('/auth/me').then((response) => response.data),
};
