let uploadFolder=null;
window.addEventListener('openUpload',e=>{uploadFolder=e.detail.folder||null;const el=document.getElementById('uploadModal');if(el)new bootstrap.Modal(el).show();});
document.addEventListener('DOMContentLoaded',()=>{
 const input=document.getElementById('fileInput'), dz=document.getElementById('dropzone');
 if(!input)return;
 input.addEventListener('change',()=>uploadFiles(input.files));
 ['dragenter','dragover'].forEach(x=>dz.addEventListener(x,e=>{e.preventDefault();dz.classList.add('drag')}));
 ['dragleave','drop'].forEach(x=>dz.addEventListener(x,e=>{e.preventDefault();dz.classList.remove('drag')}));
 dz.addEventListener('drop',e=>uploadFiles(e.dataTransfer.files));
 const v=localStorage.getItem('flux_view');if(v)setFileView(v);
});
async function uploadFiles(files){if(!files?.length)return;const q=document.getElementById('uploadQueue');for(const f of files){const row=document.createElement('div');row.className='upload-row';row.innerHTML=`<div><strong>${escapeHtml(f.name)}</strong><small>${humanUploadBytes(f.size)}</small></div><div class="progress flex-grow-1"><div class="progress-bar" style="width:0%"></div></div>`;q.appendChild(row);}
const fd=new FormData();for(const f of files)fd.append('files[]',f);fd.append('_csrf',csrf());if(uploadFolder)fd.append('folder_id',uploadFolder);
try{const d=await api('upload.php',{method:'POST',body:fd});d.uploaded.forEach(()=>{});toast(`${d.uploaded.length} file(s) uploaded`);setTimeout(()=>location.reload(),600);}catch(e){toast(e.message,'error')}}
function humanUploadBytes(n){let u=['B','KB','MB','GB'],i=0;while(n>=1024&&i<3){n/=1024;i++;}return n.toFixed(1)+' '+u[i];}
