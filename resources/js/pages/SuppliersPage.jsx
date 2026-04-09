import React, { useEffect, useState, useCallback } from 'react'
import api from '../api/client.js'
import Modal from '../components/Modal.jsx'

const EMPTY_FORM = { name: '' }

export default function SuppliersPage() {
  const [items, setItems] = useState([])
  const [meta, setMeta] = useState({})
  const [page, setPage] = useState(1)
  const [loading, setLoading] = useState(true)
  const [modalType, setModalType] = useState(null)
  const [selected, setSelected] = useState(null)
  const [form, setForm] = useState(EMPTY_FORM)
  const [formErrors, setFormErrors] = useState({})
  const [saving, setSaving] = useState(false)

  const fetchItems = useCallback(async () => {
    setLoading(true)
    try {
      const res = await api.get(`/suppliers?page=${page}&per_page=10`)
      setItems(res.data.data)
      setMeta(res.data.meta)
    } catch {
      // silent
    } finally {
      setLoading(false)
    }
  }, [page])

  useEffect(() => { fetchItems() }, [fetchItems])

  const openCreate = () => { setForm(EMPTY_FORM); setFormErrors({}); setModalType('create') }
  const openEdit = (item) => { setSelected(item); setForm({ name: item.name }); setFormErrors({}); setModalType('edit') }
  const openDelete = (item) => { setSelected(item); setModalType('delete') }
  const closeModal = () => { setModalType(null); setSelected(null); setFormErrors({}) }

  const handleSave = async () => {
    setSaving(true)
    try {
      if (modalType === 'create') {
        await api.post('/suppliers', form)
      } else {
        await api.put(`/suppliers/${selected.id}`, form)
      }
      closeModal()
      fetchItems()
    } catch (err) {
      if (err.response?.status === 422) setFormErrors(err.response.data.errors ?? {})
    } finally {
      setSaving(false)
    }
  }

  const handleDelete = async () => {
    setSaving(true)
    try {
      await api.delete(`/suppliers/${selected.id}`)
      closeModal(); fetchItems()
    } finally { setSaving(false) }
  }

  return (
    <div className="space-y-4">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
          <h1 className="text-2xl font-bold text-slate-900">Suppliers</h1>
          <p className="text-slate-500 text-sm mt-0.5">{meta.total ?? '…'} total suppliers</p>
        </div>
        <button onClick={openCreate} className="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition-colors">
          + New Supplier
        </button>
      </div>

      <div className="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div className="overflow-x-auto">
          {loading ? (
            <div className="p-8 text-center text-slate-400">Loading...</div>
          ) : items.length === 0 ? (
            <div className="p-8 text-center text-slate-400">No suppliers found.</div>
          ) : (
            <table className="w-full text-sm">
              <thead>
                <tr className="bg-slate-50 text-slate-600 text-left">
                  <th className="px-6 py-3 font-medium">#</th>
                  <th className="px-6 py-3 font-medium">Name</th>
                  <th className="px-6 py-3 font-medium">Created</th>
                  <th className="px-6 py-3 font-medium text-right">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100">
                {items.map((item) => (
                  <tr key={item.id} className="hover:bg-slate-50 transition-colors">
                    <td className="px-6 py-3 text-slate-400">{item.id}</td>
                    <td className="px-6 py-3 font-medium text-slate-900">{item.name}</td>
                    <td className="px-6 py-3 text-slate-500">{new Date(item.created_at).toLocaleDateString()}</td>
                    <td className="px-6 py-3 text-right">
                      <button onClick={() => openEdit(item)} className="text-blue-600 hover:text-blue-800 font-medium mr-4">Edit</button>
                      <button onClick={() => openDelete(item)} className="text-red-500 hover:text-red-700 font-medium">Delete</button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          )}
        </div>
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

      <Modal isOpen={modalType === 'create' || modalType === 'edit'} onClose={closeModal} title={modalType === 'create' ? 'New Supplier' : 'Edit Supplier'} size="sm">
        <div className="space-y-4">
          <div>
            <label className="block text-sm font-medium text-slate-700 mb-1">Name</label>
            <input
              type="text"
              value={form.name}
              onChange={(e) => { setForm({ name: e.target.value }); setFormErrors({}) }}
              placeholder="Supplier name"
              className={`w-full px-4 py-2.5 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 ${formErrors.name ? 'border-red-400 bg-red-50' : 'border-slate-300'}`}
            />
            {formErrors.name && <p className="mt-1 text-xs text-red-500">{formErrors.name[0]}</p>}
          </div>
          <div className="flex justify-end gap-3 pt-2">
            <button onClick={closeModal} className="px-4 py-2 border border-slate-300 rounded-xl text-sm text-slate-700 hover:bg-slate-50">Cancel</button>
            <button onClick={handleSave} disabled={saving} className="px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-300 text-white rounded-xl text-sm font-semibold transition-colors">
              {saving ? 'Saving...' : 'Save'}
            </button>
          </div>
        </div>
      </Modal>

      <Modal isOpen={modalType === 'delete'} onClose={closeModal} title="Delete Supplier" size="sm">
        <p className="text-slate-700 mb-6">Delete <strong>{selected?.name}</strong>? This action cannot be undone.</p>
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
