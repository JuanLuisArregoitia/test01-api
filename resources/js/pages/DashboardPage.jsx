import React, { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import api from '../api/client.js'

function StatCard({ label, count, icon, to, color }) {
  return (
    <Link
      to={to}
      className={`bg-white rounded-2xl p-6 shadow-sm border border-slate-100 hover:shadow-md transition-shadow flex items-center gap-4`}
    >
      <div className={`w-12 h-12 rounded-xl ${color} flex items-center justify-center text-2xl shrink-0`}>
        {icon}
      </div>
      <div>
        <p className="text-2xl font-bold text-slate-900">{count ?? '—'}</p>
        <p className="text-sm text-slate-500 mt-0.5">{label}</p>
      </div>
    </Link>
  )
}

export default function DashboardPage() {
  const [stats, setStats] = useState({})
  const [recentOrders, setRecentOrders] = useState([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    const fetchStats = async () => {
      try {
        const [clients, suppliers, products, orders] = await Promise.all([
          api.get('/clients?per_page=1'),
          api.get('/suppliers?per_page=1'),
          api.get('/products?per_page=1'),
          api.get('/orders?per_page=5'),
        ])
        setStats({
          clients: clients.data.meta?.total,
          suppliers: suppliers.data.meta?.total,
          products: products.data.meta?.total,
          orders: orders.data.meta?.total,
        })
        setRecentOrders(orders.data.data ?? [])
      } catch {
        // silent fail
      } finally {
        setLoading(false)
      }
    }
    fetchStats()
  }, [])

  const statCards = [
    { label: 'Clients', key: 'clients', icon: '👥', to: '/clients', color: 'bg-blue-100' },
    { label: 'Suppliers', key: 'suppliers', icon: '🏭', to: '/suppliers', color: 'bg-indigo-100' },
    { label: 'Products', key: 'products', icon: '📦', to: '/products', color: 'bg-sky-100' },
    { label: 'Orders', key: 'orders', icon: '🛒', to: '/orders', color: 'bg-cyan-100' },
  ]

  const statusLabel = (id) => {
    const map = { 1: { label: 'Pending', cls: 'bg-yellow-100 text-yellow-700' }, 2: { label: 'Processing', cls: 'bg-blue-100 text-blue-700' }, 3: { label: 'Completed', cls: 'bg-green-100 text-green-700' } }
    return map[id] ?? { label: 'Unknown', cls: 'bg-slate-100 text-slate-700' }
  }

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold text-slate-900">Dashboard</h1>
        <p className="text-slate-500 text-sm mt-1">Overview of your system</p>
      </div>

      {/* Stats */}
      <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        {statCards.map(({ label, key, icon, to, color }) => (
          <StatCard key={key} label={label} count={loading ? null : stats[key]} icon={icon} to={to} color={color} />
        ))}
      </div>

      {/* Recent Orders */}
      <div className="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div className="flex items-center justify-between px-6 py-4 border-b border-slate-100">
          <h2 className="font-semibold text-slate-800">Recent Orders</h2>
          <Link to="/orders" className="text-sm text-blue-600 hover:text-blue-700 font-medium">View all →</Link>
        </div>
        <div className="overflow-x-auto">
          {loading ? (
            <div className="p-6 text-center text-slate-400 text-sm">Loading...</div>
          ) : recentOrders.length === 0 ? (
            <div className="p-6 text-center text-slate-400 text-sm">No orders yet.</div>
          ) : (
            <table className="w-full text-sm">
              <thead>
                <tr className="bg-slate-50 text-slate-600 text-left">
                  <th className="px-6 py-3 font-medium">Order #</th>
                  <th className="px-6 py-3 font-medium">Status</th>
                  <th className="px-6 py-3 font-medium">Client ID</th>
                  <th className="px-6 py-3 font-medium">Date</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100">
                {recentOrders.map((order) => {
                  const { label, cls } = statusLabel(order.status_id)
                  return (
                    <tr key={order.id} className="hover:bg-slate-50 transition-colors">
                      <td className="px-6 py-3 font-mono font-medium text-slate-900">{order.order_number}</td>
                      <td className="px-6 py-3">
                        <span className={`px-2.5 py-0.5 rounded-full text-xs font-medium ${cls}`}>{label}</span>
                      </td>
                      <td className="px-6 py-3 text-slate-600">{order.client_id}</td>
                      <td className="px-6 py-3 text-slate-500">{new Date(order.created_at).toLocaleDateString()}</td>
                    </tr>
                  )
                })}
              </tbody>
            </table>
          )}
        </div>
      </div>
    </div>
  )
}
