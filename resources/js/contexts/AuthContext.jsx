import React, { createContext, useContext, useState } from 'react'
import api from '../api/client.js'

const AuthContext = createContext(null)

export function AuthProvider({ children }) {
  const [token, setToken] = useState(() => localStorage.getItem('auth_token'))

  const login = async (email, password) => {
    const res = await api.post('/login', { email, password })
    const { access_token } = res.data
    localStorage.setItem('auth_token', access_token)
    setToken(access_token)
    return res.data
  }

  const register = async (data) => {
    const res = await api.post('/register', data)
    const { access_token } = res.data
    localStorage.setItem('auth_token', access_token)
    setToken(access_token)
    return res.data
  }

  const logout = async () => {
    try {
      await api.post('/logout')
    } catch {
      // ignore errors on logout
    }
    localStorage.removeItem('auth_token')
    setToken(null)
  }

  return (
    <AuthContext.Provider value={{ token, isAuthenticated: !!token, login, register, logout }}>
      {children}
    </AuthContext.Provider>
  )
}

export const useAuth = () => useContext(AuthContext)
