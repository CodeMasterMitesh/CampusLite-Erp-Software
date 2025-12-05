// employee.js
function initEmployee(){ try { initAdvancedTable('#employee-table'); } catch(e){ console.error('initEmployee failed', e); } const c=document.querySelector('.dashboard-container'); if(c) c.classList.add('show'); }
if(document.readyState==='loading') document.addEventListener('DOMContentLoaded', initEmployee); else try{ initEmployee(); }catch(e){console.error(e);} 

function exportToExcel(){ if(window.CRUD && CRUD.showLoading) CRUD.showLoading('tableContainer'); setTimeout(()=>{ window.location.href='?page=employee&export=excel'; if(window.CRUD && CRUD.hideLoading) CRUD.hideLoading(); },1000); }
function printTable(){ const table=document.getElementById('employee-table').cloneNode(true); const printWindow=window.open('','_blank'); printWindow.document.write(`<html><head><title>Employee</title></head><body><h2>Employee</h2>${table.outerHTML}</body></html>`); printWindow.document.close(); printWindow.print(); }
function refreshTable(){ if(window.CRUD && CRUD.showLoading) CRUD.showLoading('tableContainer'); setTimeout(()=>location.reload(),1000); }

async function editEmployee(id){
	if(window.CRUD && CRUD.showLoading) CRUD.showLoading('tableContainer');
	try{
		const res=await CRUD.get(`api/employee.php?action=get&id=${encodeURIComponent(id)}`);
		if(res.success&&res.data){
			const e=res.data;
			const setVal = (sel,val)=>{ const el=document.querySelector(sel); if(el){ if (el.type === 'file') { /* never set non-empty programmatically */ el.value = ''; } else { el.value = val ?? ''; } } };
			const setSelVal = (id,val)=>{ const el=document.getElementById(id); if(el) el.value = val ?? ''; };
			const setDate = (sel,val)=>{ const el=document.querySelector(sel); if(el) el.value = (val||'').slice(0,10); };
			// core fields
			setSelVal('employeeId', e.id);
			setVal('#addEmployeeForm [name="name"]', e.name);
			setVal('#addEmployeeForm [name="email"]', e.email);
			setVal('#addEmployeeForm [name="mobile"]', e.mobile||e.phone);
			setSelVal('employeeBranch', e.branch_id||0);
			// profile/HR fields
			setDate('#addEmployeeForm [name="dob"]', e.dob);
			setVal('#addEmployeeForm [name="gender"]', e.gender);
			setVal('#addEmployeeForm [name="marital_status"]', e.marital_status);
			setDate('#addEmployeeForm [name="joining_date"]', e.joining_date);
			setDate('#addEmployeeForm [name="resign_date"]', e.resign_date);
			setVal('#addEmployeeForm [name="in_time"]', e.in_time);
			setVal('#addEmployeeForm [name="out_time"]', e.out_time);
			setVal('#addEmployeeForm [name="address"]', e.address);
			setVal('#addEmployeeForm [name="area"]', e.area);
			setVal('#addEmployeeForm [name="city"]', e.city);
			setVal('#addEmployeeForm [name="pincode"]', e.pincode);
			setVal('#addEmployeeForm [name="state"]', e.state);
			setVal('#addEmployeeForm [name="country"]', e.country);
			setVal('#addEmployeeForm [name="aadhar_card"]', e.aadhar_card);
			setVal('#addEmployeeForm [name="pan_card"]', e.pan_card);
			setVal('#addEmployeeForm [name="passport"]', e.passport);
			// photo preview
			const img = document.getElementById('employeePhotoPreview');
			const removeBtn = document.getElementById('removeEmployeePhoto');
			if (img) {
				if (e.profile_photo) { img.src = '/public/uploads/employees/' + e.profile_photo; img.style.display=''; if (removeBtn) removeBtn.style.display=''; }
				else { img.src=''; img.style.display='none'; if (removeBtn) removeBtn.style.display='none'; }
			}
			// show existing document filenames as links (no file value set)
			const setDocInfo = (id, filename)=>{
				const el=document.getElementById(id); if(!el) return;
				if(filename){
					const url = '/public/uploads/employees/' + filename;
					el.innerHTML = `Existing: <a href="${url}" target="_blank" rel="noopener">${filename}</a>`;
					el.style.display='';
				} else { el.textContent=''; el.style.display='none'; }
			};
			setDocInfo('aadharFileInfo', e.aadhar_card);
			setDocInfo('panFileInfo', e.pan_card);
			setDocInfo('passportFileInfo', e.passport);
			// reset file input (allowed to set only empty string)
			const fileInput = document.getElementById('employeePhotoInput'); if (fileInput) fileInput.value='';
			// prefill education and employment rows
			const eduWrap = document.getElementById('educationList'); if (eduWrap) eduWrap.innerHTML='';
			const empWrap = document.getElementById('employmentList'); if (empWrap) empWrap.innerHTML='';
			if (Array.isArray(e.education)) e.education.forEach(item=>addEducationRow(item)); else addEducationRow();
			if (Array.isArray(e.employment)) e.employment.forEach(item=>addEmploymentRow(item));
			// enable form and set modal
			const form=document.getElementById('addEmployeeForm'); if(form) Array.from(form.elements).forEach(el=>el.disabled=false);
			const titleEl=document.querySelector('#addEmployeeModal .modal-title'); if(titleEl) titleEl.innerText='Edit Employee';
			const saveBtn=document.querySelector('#addEmployeeModal .btn-primary'); if(saveBtn){ saveBtn.style.display=''; saveBtn.innerText='Update Employee'; }
			bootstrap.Modal.getOrCreateInstance(document.getElementById('addEmployeeModal')).show();
		} else { window.CRUD && CRUD.toastError && CRUD.toastError('Employee not found'); }
	}catch(e){ window.CRUD && CRUD.toastError && CRUD.toastError('Failed: '+e.message); }
	finally{ window.CRUD && CRUD.hideLoading && CRUD.hideLoading(); }
}

async function viewEmployee(id){ if(window.CRUD && CRUD.showLoading) CRUD.showLoading('tableContainer'); try{ const res=await CRUD.get(`api/employee.php?action=get&id=${encodeURIComponent(id)}`); if(res.success&&res.data){ const e=res.data; document.getElementById('employeeId').value=e.id||''; document.querySelector('#addEmployeeForm [name="name"]').value=e.name||''; document.querySelector('#addEmployeeForm [name="email"]').value=e.email||''; document.querySelector('#addEmployeeForm [name="phone"]').value=e.mobile||e.phone||''; document.getElementById('employeeBranch') && (document.getElementById('employeeBranch').value=e.branch_id||0); const form=document.getElementById('addEmployeeForm'); if(form) Array.from(form.elements).forEach(el=>el.disabled=true); const saveBtn=document.querySelector('#addEmployeeModal .btn-primary'); if(saveBtn) saveBtn.style.display='none'; document.querySelector('#addEmployeeModal .modal-title') && (document.querySelector('#addEmployeeModal .modal-title').innerText='View Employee'); bootstrap.Modal.getOrCreateInstance(document.getElementById('addEmployeeModal')).show(); } else { window.CRUD && CRUD.toastError && CRUD.toastError('Employee not found'); } }catch(e){ window.CRUD && CRUD.toastError && CRUD.toastError('Failed: '+e.message); } finally{ window.CRUD && CRUD.hideLoading && CRUD.hideLoading(); } }

async function deleteEmployee(id){ if(!confirm('Delete employee '+id+'?')) return; if(window.CRUD && CRUD.showLoading) CRUD.showLoading('tableContainer'); try{ const p=new URLSearchParams(); p.append('id', id); const res=await CRUD.post('api/employee.php?action=delete', p); if(res.success){ window.CRUD && CRUD.toastSuccess && CRUD.toastSuccess('Deleted'); refreshTable(); } else window.CRUD && CRUD.toastError && CRUD.toastError('Delete failed'); }catch(e){ window.CRUD && CRUD.toastError && CRUD.toastError('Delete failed: '+e.message);} finally{ window.CRUD && CRUD.hideLoading && CRUD.hideLoading(); } }


// Dynamic rows for Education and Employment
function addEducationRow(prefill){ const wrap=document.getElementById('educationList'); if(!wrap) return; const row=document.createElement('div'); row.className='row g-2 align-items-end mb-2'; row.innerHTML=`
	<div class="col-md-2"><input class="form-control" placeholder="Degree" value="${prefill?.degree||''}"></div>
	<div class="col-md-3"><input class="form-control" placeholder="University/Institute" value="${prefill?.institute||''}"></div>
	<div class="col-md-2"><input type="date" class="form-control" placeholder="From" value="${prefill?.from_date||''}"></div>
	<div class="col-md-2"><input type="date" class="form-control" placeholder="To" value="${prefill?.to_date||''}"></div>
	<div class="col-md-1"><input class="form-control" placeholder="Grade" value="${prefill?.grade||''}"></div>
	<div class="col-md-2"><input class="form-control" placeholder="Specialization" value="${prefill?.specialization||''}"></div>
	<div class="col-auto"><button type="button" class="btn btn-sm btn-outline-danger">Remove</button></div>`;
	row.querySelector('button').addEventListener('click',()=>row.remove());
	wrap.appendChild(row);
}
function addEmploymentRow(prefill){ const wrap=document.getElementById('employmentList'); if(!wrap) return; const row=document.createElement('div'); row.className='row g-2 align-items-end mb-2'; row.innerHTML=`
	<div class="col-md-3"><input class="form-control" placeholder="Organisation" value="${prefill?.organisation||''}"></div>
	<div class="col-md-3"><input class="form-control" placeholder="Designation" value="${prefill?.designation||''}"></div>
	<div class="col-md-2"><input type="date" class="form-control" placeholder="From" value="${prefill?.from_date||''}"></div>
	<div class="col-md-2"><input type="date" class="form-control" placeholder="To" value="${prefill?.to_date||''}"></div>
	<div class="col-md-2"><input type="number" step="0.01" class="form-control" placeholder="Annual CTC" value="${prefill?.annual_ctc||''}"></div>
	<div class="col-auto"><button type="button" class="btn btn-sm btn-outline-danger">Remove</button></div>`;
	row.querySelector('button').addEventListener('click',()=>row.remove());
	wrap.appendChild(row);
}

function collectEducation(){
	const wrap=document.getElementById('educationList'); if(!wrap) return [];
	const rows=[...wrap.children];
	return rows
		.map(r=>{
			const inputs=[...r.querySelectorAll('input')];
			return {
				degree:inputs[0].value.trim(),
				institute:inputs[1].value.trim(),
				from_date:inputs[2].value,
				to_date:inputs[3].value,
				grade:inputs[4].value.trim(),
				specialization:inputs[5].value.trim()
			};
		})
		// keep rows that have at least one non-empty field
		.filter(row => Object.values(row).some(v => (v||'').trim() !== ''));
}
function collectEmployment(){
	const wrap=document.getElementById('employmentList'); if(!wrap) return [];
	const rows=[...wrap.children];
	return rows
		.map(r=>{
			const inputs=[...r.querySelectorAll('input')];
			return {
				organisation:inputs[0].value.trim(),
				designation:inputs[1].value.trim(),
				from_date:inputs[2].value,
				to_date:inputs[3].value,
				annual_ctc:inputs[4].value
			};
		})
		// keep rows that have at least one non-empty field
		.filter(row => Object.values(row).some(v => (v||'').toString().trim() !== ''));
}

// Hook add-more buttons (guard to avoid duplicate listeners if script loads twice)
if (!window.__employeeRowHandlersAttached) {
	window.__employeeRowHandlersAttached = true;
	document.addEventListener('click', function(e){
		if(e.target && e.target.id==='addEducationRow') addEducationRow();
		if(e.target && e.target.id==='addEmploymentRow') addEmploymentRow();
	});
}

// Override saveEmployee to inject nested arrays
// Guard to avoid double-declaration when script is loaded twice
if (!window.__employeeEnhancementsApplied) { window.__employeeEnhancementsApplied = true; }

// Wrap saveEmployee only once
// Single saveEmployee definition that always sends education/employment arrays
window.saveEmployee = async function(){
	const form=document.getElementById('addEmployeeForm');
	const params=new FormData(form);
	const edu=collectEducation(); const emp=collectEmployment();
	params.append('education', JSON.stringify(edu));
	params.append('employment', JSON.stringify(emp));
	if(!params.get('name')){ window.CRUD && CRUD.toastError && CRUD.toastError('Name required'); return; }
	const modalEl=document.getElementById('addEmployeeModal'); window.CRUD && CRUD.modalLoadingStart && CRUD.modalLoadingStart(modalEl);
	try{
		const id=params.get('id'); const action=id? 'update':'create';
		const res=await CRUD.post('api/employee.php?action='+action, params);
		if(res.success){ bootstrap.Modal.getOrCreateInstance(modalEl).hide(); window.CRUD && CRUD.toastSuccess && CRUD.toastSuccess(res.message||'Saved'); refreshTable(); }
		else window.CRUD && CRUD.toastError && CRUD.toastError('Save failed: '+(res.message||res.error||'Unknown'));
	}catch(e){ window.CRUD && CRUD.toastError && CRUD.toastError('Request failed: '+e.message); }
	finally{ window.CRUD && CRUD.modalLoadingStop && CRUD.modalLoadingStop(modalEl); }
}

// Photo preview and remove handling
document.addEventListener('change', function(e){
	if (e.target && e.target.id === 'employeePhotoInput') {
		const input = e.target; const file = input.files && input.files[0];
		const img = document.getElementById('employeePhotoPreview');
		const removeBtn = document.getElementById('removeEmployeePhoto');
		if (file) {
			const reader = new FileReader();
			reader.onload = function(ev){ img.src = ev.target.result; img.style.display=''; removeBtn.style.display=''; };
			reader.readAsDataURL(file);
		} else { img.src=''; img.style.display='none'; removeBtn.style.display='none'; }
	}
});

document.addEventListener('click', async function(e){
	if (e.target && e.target.id === 'removeEmployeePhoto') {
		const id = document.getElementById('employeeId').value;
		// If new file selected but not saved yet, just clear selection and preview
		const input = document.getElementById('employeePhotoInput');
		if (input && input.value) { input.value = ''; }
		const img = document.getElementById('employeePhotoPreview'); if (img) { img.src=''; img.style.display='none'; }
		e.target.style.display='none';
		// If existing employee, call API to remove stored photo
		if (id) {
			try { const res = await CRUD.post('api/employee.php?action=delete-photo', new URLSearchParams({id})); if (res.success) { window.CRUD && CRUD.toastSuccess && CRUD.toastSuccess('Photo removed'); } else { window.CRUD && CRUD.toastError && CRUD.toastError(res.message||'Failed to remove'); } } catch(err) { window.CRUD && CRUD.toastError && CRUD.toastError('Remove failed: '+err.message); }
		}
	}
});
