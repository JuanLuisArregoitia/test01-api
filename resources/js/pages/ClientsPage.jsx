import React, { useEffect, useState, useCallback } from 'react'
import api from '../api/client.js'
import Modal from '../components/Modal.jsx'

const EMPTY_FORM = { name: '', lastname: '', email: '' }

function FormField({ label, name, value, onChange, errors, type = 'text', ...rest }) {
  return (
    <div>
      <label className="block text-sm font-medium text-slate-700 mb-1">{label}</label>
      <input
        type={type}
        name={name}
        value={value}
        onChange={onChange}
        className={`w-full px-4 py-2.5 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-shadow
          ${errors[name] ? 'border-red-400 bg-red-50' : 'border-slate-300'}`}
        {...rest}
      />
      {errors[name] && <p className="mt-1 text-xs text-red-500">{errors[name][0]}</p>}
    </div>
  )
}

export default function ClientsPage() {
  const [clients, setClients] = useState([])
  const [meta, setMeta] = useState({})
  const [page, setPage] = useState(1)
  const [loading, setLoading] = useState(true)
  const [modalType, setModalType] = useState(null) // 'create' | 'edit' | 'delete'
  const [selected, setSelected] = useState(null)
  const [form, setForm] = useState(EMPTY_FORM)
  const [formErrors, setFormErrors] = useState({})
  const [saving, setSaving] = useState(false)

  const fetchClients = useCallback(async () => {
    setLoading(true)
    try {
      const res = await api.get(`/clients?page=${page}&per_page=10`)
      setClients(res.data.data)
      setMeta(res.data.meta)
    } catch {
      // silent
    } finally {
      setLoading(false)
    }
  }, [page])

  useEffect(() => { fetchClients() }, [fetchClients])

  const openCreate = () => { setForm(EMPTY_FORM); setFormErrors({}); setModalType('create') }
  const openEdit = (c) => { setSelected(c); setForm({ name: c.name, lastname: c.lastname, email: c.email }); setFormErrors({}); setModalType('edit') }
  const openDelete = (c) => { setSelected(c); setModalType('delete') }
  const closeModal = () => { setModalType(null); setSelected(null); setFormErrors({}) }

  const handleChange = (e) => {
    setForm((p) => ({ ...p, [e.target.name]: e.target.value }))
    setFormErrors((p) => ({ ...p, [e.target.name]: '' }))
  }

  const handleSave = async () => {
    setSaving(true)
    try {
      if (modalType === 'create') {
        await api.post('/clients', form)
      } else {
        await api.put(`/clients/${selected.id}`, form)
      }
      closeModal()
      fetchClients()
    } catch (err) {
      if (err.response?.status === 422) {
        setFormErrors(err.response.data.errors ?? {})
      }
    } finally {
      setSaving(false)
    }
  }

  const handleDelete = async () => {
    setSaving(true)
    try {
      await api.delete(`/clients/${selected.id}`)
      closeModal()
      fetchClients()
    } finally {
      setSaving(false)
    }
  }

  return (
    <div className="space-y-4">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
          <h1 className="text-2xl font-bold text-slate-900">Clients</h1>
          <p className="text-slate-500 text-sm mt-0.5">{meta.total ?? '…'} total clients</p>
        </div>
        <button
          onClick={openCreate}
          className="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition-colors"
        >
          + New Client
        </button>
      </div>

      {/* Table */}
      <div className="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div className="overflow-x-auto">
          {loading ? (
            <div className="p-8 text-center text-slate-400">Loading...</div>
          ) : clients.length === 0 ? (
            <div className="p-8 text-center text-slate-400">No clients found. Create the first one!</div>
          ) : (
            <table className="w-full text-sm">
              <thead>
                <tr className="bg-slate-50 text-slate-600 text-left">
                  <th className="px-6 py-3 font-medium">#</th>
                  <th className="px-6 py-3 font-medium">Name</th>
                  <th className="px-6 py-3 font-medium">Last Name</th>
                  <th className="px-6 py-3 font-medium">Email</th>
                  <th className="px-6 py-3 font-medium text-right">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100">
                {clients.map((c) => (
                  <tr key={c.id} className="hover:bg-slate-50 transition-colors">
                    <td className="px-6 py-3 text-slate-400">{c.id}</td>
                    <td className="px-6 py-3 font-medium text-slate-900">{c.name}</td>
                    <td className="px-6 py-3 text-slate-700">{c.lastname}</td>
                    <td className="px-6 py-3 text-slate-600">{c.email}</td>
                    <td className="px-6 py-3 text-right">
                      <button onClick={() => openEdit(c)} className="text-blue-600 hover:text-blue-800 font-medium mr-4">Edit</button>
                      <button onClick={() => openDelete(c)} className="text-red-500 hover:text-red-700 font-medium">Delete</button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          )}
        </div>

        {/* Pagination */}
        {meta.last_page > 1 && (
          <div className="flex items-center justify-between px-6 py-3 border-t border-slate-100 text-sm text-slate-600">
            <span>Page {meta.current_page} of {meta.last_page}</span>
            <div className="flex gap-2">
              <button disabled={page <= 1} onClick={() => setPage((p) => p - 1)} className="px-3 py-1 border border-slate-300 rounded-lg hover:bg-slate-50 disabled:opacity-40">Prev</button>
              <button disabled={page >= meta.last_page} onClick={() => setPage((p) => p + 1)} className="px-3 py-1 border border-slate-300 rounded-lg hover:bg-slate-50 disabled:opacity-40">Next</button>
            </div>
          </div>
        )}
      </div>

      {/* Create / Edit Modal */}
      <Modal isOpen={modalType === 'create' || modalType === 'edit'} onClose={closeModal} title={modalType === 'create' ? 'New Client' : 'Edit Client'}>
        <div className="space-y-4">
          <FormField label="Name" name="name" value={form.name} onChange={handleChange} errors={formErrors} placeholder="John" />
          <FormField label="Last Name" name="lastname" value={form.lastname} onChange={handleChange} errors={formErrors} placeholder="Doe" />
          <FormField label="Email" name="email" type="email" value={form.email} onChange={handleChange} errors={formErrors} placeholder="john@example.com" />
          <div className="flex justify-end gap-3 pt-2">
            <button onClick={closeModal} className="px-4 py-2 border border-slate-300 rounded-xl text-sm text-slate-700 hover:bg-slate-50">Cancel</button>
            <button onClick={handleSave} disabled={saving} className="px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-300 text-white rounded-xl text-sm font-semibold transition-colors">
              {saving ? 'Saving...' : 'Save'}
            </button>
          </div>
        </div>
      </Modal>

      {/* Delete Confirm Modal */}
      <Modal isOpen={modalType === 'delete'} onClose={closeModal} title="Delete Client" size="sm">
        <p className="text-slate-700 mb-6">
          Are you sure you want to delete <strong>{selected?.name} {selected?.lastname}</strong>? This action cannot be undone.
        </p>
        <div className="flex justify-end gap-3">
          <button onClick={closeModal} className="px-4 py-2 border border-slate-300 rounded-xl text-sm text-slate-700 hover:bg-slate-50">Cancel</button>
          <button onClick={handleDelete} disabled={saving} className="px-4 py-2 bg-red-600 hover:bg-red-700 disabled:bg-red-300 text-white rounded-xl text-sm font-semibold transition-colors">
            {saving ? 'Deleting...' : 'Delete'}
          </button>
        </div>
      </Modal>
    </div>
  )
}
