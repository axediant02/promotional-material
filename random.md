import React, { useState } from 'react';
import { 
  Folder, 
  FileText, 
  Image as ImageIcon, 
  Video, 
  UploadCloud, 
  Grid, 
  List, 
  Search, 
  Bell, 
  LogOut,
  ChevronLeft,
  Pin,
  Clock,
  MoreVertical,
  Plus,
  X,
  CheckCircle,
  ChevronDown
} from 'lucide-react';

// --- MOCK DATA ---

const INITIAL_PINNED_TASKS = [
  {
    id: 'REQ-802',
    client: 'Client One',
    title: 'Update Hero Banners for Q3 Campaign',
    description: 'Need new high-res banners reflecting the new brand guidelines. Ensure all assets are optimized for web and mobile.',
    dueDate: 'Tomorrow, 5:00 PM',
    status: 'In Progress',
    priority: 'High'
  },
  {
    id: 'REQ-805',
    client: 'Client Two',
    title: 'Social Media Video Edits',
    description: 'Cut down the main promo video into 15-second segments for TikTok and Instagram Reels. Add baked-in captions.',
    dueDate: 'Friday, 12:00 PM',
    status: 'Pending',
    priority: 'Medium'
  }
];

const ALL_TASKS = [
  {
    id: '#019DD6DF',
    title: 'Brand Assets Review',
    folder: 'Brand Assets Q3',
    tags: ['PENDING', 'NEEDS ATTENTION'],
    client: 'Client One',
    type: 'New asset',
    dueDate: 'May 5',
    status: 'UNASSIGNED',
    actionText: 'ASSIGN TO ME'
  },
  {
    id: '#019DD351',
    title: 'Social Media Video Edits',
    folder: 'Social Media Pack',
    tags: ['IN PROGRESS'],
    client: 'Client Two',
    type: 'Update asset',
    dueDate: 'May 1',
    status: 'ASSIGNED',
    actionText: 'UPDATE DUE'
  },
  {
    id: '#019DD352',
    title: 'Corporate Video Final Cut',
    folder: 'Product Launch Video',
    tags: ['DONE'],
    client: 'Acme Corp',
    type: 'New asset',
    dueDate: 'Apr 28',
    status: 'ASSIGNED',
    actionText: 'VIEW DETAILS'
  },
  {
    id: '#019DD353',
    title: 'Website Banner Export',
    folder: 'Website Redesign',
    tags: ['PENDING'],
    client: 'Global Tech',
    type: 'Export',
    dueDate: 'May 12',
    status: 'ASSIGNED',
    actionText: 'UPDATE DUE'
  }
];

const CLIENT_FOLDERS = [
  { id: 'f1', name: 'Brand Assets Q3', client: 'Client One', files: 12, updated: '2 hours ago', color: 'border-indigo-500/50' },
  { id: 'f2', name: 'Social Media Pack', client: 'Client Two', files: 45, updated: 'Yesterday', color: 'border-pink-500/50' },
  { id: 'f3', name: 'Product Launch Video', client: 'Acme Corp', files: 3, updated: 'Apr 28, 2026', color: 'border-blue-500/50' },
  { id: 'f4', name: 'Website Redesign', client: 'Global Tech', files: 128, updated: 'Apr 25, 2026', color: 'border-purple-500/50' },
  { id: 'f5', name: 'Event Photography', client: 'Client One', files: 340, updated: 'Apr 20, 2026', color: 'border-emerald-500/50' },
  { id: 'f6', name: 'Print Collateral', client: 'Local Biz', files: 8, updated: 'Apr 15, 2026', color: 'border-orange-500/50' },
];

const FOLDER_CONTENTS = [
  { id: 'c1', name: 'hero-banner-v1.jpg', type: 'image', date: '4/28/2026', size: '2.4 MB' },
  { id: 'c2', name: 'hero-banner-mobile.jpg', type: 'image', date: '4/28/2026', size: '1.1 MB' },
  { id: 'c3', name: 'brand-guidelines.pdf', type: 'pdf', date: '4/27/2026', size: '5.6 MB' },
  { id: 'c4', name: 'b-roll-footage.mp4', type: 'video', date: '4/26/2026', size: '145 MB' },
];

// --- COMPONENTS ---

export default function ProductionDashboard() {
  const [viewMode, setViewMode] = useState('grid'); // 'grid' | 'list'
  const [activeFolder, setActiveFolder] = useState(null); // null means overview
  const [activeTab, setActiveTab] = useState('overview'); // 'overview' | 'tasks'
  const [pinnedTasks, setPinnedTasks] = useState(INITIAL_PINNED_TASKS);
  const [selectedTask, setSelectedTask] = useState(null);

  // Navigation handlers
  const openFolder = (folder) => {
    setActiveFolder(folder);
    setActiveTab('overview');
  };
  const goBackToOverview = () => {
    setActiveFolder(null);
    setActiveTab('overview');
  };
  const goToTasks = () => setActiveTab('tasks');

  const handleUpdateTaskStatus = (taskId, newStatus) => {
    setPinnedTasks(prev => prev.map(t => t.id === taskId ? { ...t, status: newStatus } : t));
    setSelectedTask(prev => prev ? { ...prev, status: newStatus } : null);
  };

  return (
    <div className="min-h-screen bg-[#0b0d14] text-slate-200 font-sans selection:bg-indigo-500/30">
      
      {/* Top Navigation Bar */}
      <header className="sticky top-0 z-50 bg-[#12141d]/80 backdrop-blur-md border-b border-slate-800/50 px-6 py-4 flex items-center justify-between">
        <div className="flex items-center gap-8">
          <div className="flex items-center gap-2">
            <div className="w-8 h-8 rounded bg-indigo-600 flex items-center justify-center font-bold text-white tracking-wider">
              PR
            </div>
            <div>
              <h1 className="font-bold text-lg leading-none text-white tracking-wide">Studio.</h1>
              <span className="text-[10px] uppercase tracking-widest text-indigo-400 font-semibold">Production</span>
            </div>
          </div>
          
          <nav className="hidden md:flex gap-6 text-sm font-medium text-slate-400">
            <button 
              onClick={goBackToOverview} 
              className={`hover:text-white transition-colors ${activeTab === 'overview' && !activeFolder ? 'text-white' : ''}`}
            >
              Overview
            </button>
            <button 
              onClick={goToTasks}
              className={`hover:text-white transition-colors ${activeTab === 'tasks' ? 'text-white' : ''}`}
            >
              Tasks
            </button>
            <button className="hover:text-white transition-colors">Schedule</button>
          </nav>
        </div>

        <div className="flex items-center gap-4">
          <div className="relative hidden md:block">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500" />
            <input 
              type="text" 
              placeholder="Search folders, tasks..." 
              className="bg-[#1a1d27] border border-slate-800 rounded-full pl-9 pr-4 py-1.5 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all w-64 text-slate-200 placeholder:text-slate-500"
            />
          </div>
          <button className="p-2 text-slate-400 hover:text-white transition-colors relative">
            <Bell className="w-5 h-5" />
            <span className="absolute top-1.5 right-1.5 w-2 h-2 bg-pink-500 rounded-full border border-[#12141d]"></span>
          </button>
          <div className="h-8 w-px bg-slate-800 mx-2 hidden sm:block"></div>
          <button className="flex items-center gap-2 hover:bg-slate-800/50 py-1 px-2 rounded-lg transition-colors">
            <div className="w-8 h-8 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-sm font-medium">
              PD
            </div>
            <div className="text-left hidden sm:block">
              <p className="text-sm font-medium text-white leading-tight">Prod. User</p>
              <p className="text-[10px] text-slate-500">Design Team</p>
            </div>
          </button>
        </div>
      </header>

      {/* Main Content Area */}
      <main className="max-w-7xl mx-auto px-6 py-8">
        
        {/* Render either Overview, Folder Contents, or Tasks based on state */}
        {activeTab === 'tasks' ? (
          <TasksView onOpenFolder={openFolder} />
        ) : !activeFolder ? (
          <Overview 
            viewMode={viewMode} 
            setViewMode={setViewMode} 
            onOpenFolder={openFolder} 
            pinnedTasks={pinnedTasks}
            onOpenTask={setSelectedTask}
          />
        ) : (
          <FolderContents 
            folder={activeFolder} 
            onBack={goBackToOverview} 
          />
        )}

      </main>

      {/* Task Modal */}
      {selectedTask && (
        <TaskModal 
          task={selectedTask} 
          onClose={() => setSelectedTask(null)} 
          onUpdateStatus={(newStatus) => handleUpdateTaskStatus(selectedTask.id, newStatus)}
        />
      )}
    </div>
  );
}

// --- SUB-COMPONENTS ---

function Overview({ viewMode, setViewMode, onOpenFolder, pinnedTasks, onOpenTask }) {
  return (
    <div className="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
      
      {/* Sticky Note: Latest Requests */}
      <section>
        <div className="flex items-center gap-2 mb-4">
          <Pin className="w-4 h-4 text-amber-500" />
          <h2 className="text-sm font-semibold uppercase tracking-widest text-slate-400">Pinned Tasks ({pinnedTasks.length})</h2>
        </div>
        
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          {pinnedTasks.map((task, index) => (
            <div 
              key={task.id} 
              className={`relative overflow-hidden rounded-xl bg-gradient-to-br from-[#2a2416] to-[#1e1a12] border border-amber-900/30 p-6 shadow-lg shadow-amber-900/5 transform ${index % 2 === 0 ? 'rotate-[-0.5deg]' : 'rotate-[0.5deg]'} hover:rotate-0 transition-transform duration-300 flex flex-col h-full`}
            >
              <div className="absolute top-0 left-0 w-1 h-full bg-amber-500/50"></div>
              
              <div className="flex flex-col h-full justify-between gap-6">
                <div className="space-y-2">
                  <div className="flex items-center gap-3">
                    <span className="px-2.5 py-0.5 rounded-full bg-amber-500/10 text-amber-400 text-xs font-semibold uppercase tracking-wider border border-amber-500/20">
                      {task.status}
                    </span>
                    <span className="text-sm text-amber-200/60 font-mono">{task.id}</span>
                    <span className="text-sm text-amber-200/60">•</span>
                    <span className="text-sm text-amber-200/80 font-medium">{task.client}</span>
                  </div>
                  <h3 className="text-xl font-semibold text-amber-50 leading-tight">
                    {task.title}
                  </h3>
                  <p className="text-amber-100/70 text-sm leading-relaxed line-clamp-2">
                    {task.description}
                  </p>
                </div>
                
                <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mt-auto">
                  <div className="flex items-center gap-2 text-amber-300">
                    <Clock className="w-4 h-4" />
                    <span className="text-sm font-medium">Due: {task.dueDate}</span>
                  </div>
                  <button 
                    onClick={() => onOpenTask(task)}
                    className="w-full sm:w-auto px-4 py-2 bg-amber-500 hover:bg-amber-400 text-amber-950 font-semibold rounded-lg shadow-md transition-colors flex items-center justify-center gap-2 text-sm"
                  >
                    Open Task
                  </button>
                </div>
              </div>
            </div>
          ))}
        </div>
      </section>

      {/* Client Folders Section */}
      <section className="pt-4">
        <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
          <div>
            <h2 className="text-xl font-bold text-white">Client Workspaces</h2>
            <p className="text-sm text-slate-400">Access and manage production files for your assigned clients.</p>
          </div>
          
          <div className="flex items-center bg-[#161925] border border-slate-800 p-1 rounded-lg">
            <button 
              onClick={() => setViewMode('grid')}
              className={`p-1.5 rounded-md transition-colors ${viewMode === 'grid' ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-500 hover:text-slate-300'}`}
              aria-label="Grid View"
            >
              <Grid className="w-4 h-4" />
            </button>
            <button 
              onClick={() => setViewMode('list')}
              className={`p-1.5 rounded-md transition-colors ${viewMode === 'list' ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-500 hover:text-slate-300'}`}
              aria-label="List View"
            >
              <List className="w-4 h-4" />
            </button>
          </div>
        </div>

        {/* Folders Grid/List Render */}
        {viewMode === 'grid' ? (
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            {CLIENT_FOLDERS.map(folder => (
              <FolderCard key={folder.id} folder={folder} onClick={() => onOpenFolder(folder)} />
            ))}
          </div>
        ) : (
          <div className="flex flex-col gap-3">
            {CLIENT_FOLDERS.map(folder => (
              <FolderListItem key={folder.id} folder={folder} onClick={() => onOpenFolder(folder)} />
            ))}
          </div>
        )}
      </section>
    </div>
  );
}

function FolderContents({ folder, onBack }) {
  return (
    <div className="space-y-6 animate-in fade-in slide-in-from-right-4 duration-300">
      {/* Header / Breadcrumb */}
      <div className="flex items-center gap-4">
        <button 
          onClick={onBack}
          className="p-2 rounded-lg bg-[#161925] border border-slate-800 text-slate-400 hover:text-white hover:border-slate-700 transition-colors"
        >
          <ChevronLeft className="w-5 h-5" />
        </button>
        <div>
          <div className="flex items-center gap-2 text-sm text-slate-400 mb-1">
            <span>Workspaces</span>
            <span>/</span>
            <span className="text-indigo-400">{folder.client}</span>
          </div>
          <h2 className="text-2xl font-bold text-white flex items-center gap-3">
            <Folder className="w-7 h-7 text-indigo-500" />
            {folder.name}
          </h2>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        {/* Main Content: Files */}
        <div className="lg:col-span-2 space-y-6">
          <div className="flex items-center justify-between">
            <h3 className="text-lg font-semibold text-slate-200">Asset Catalog</h3>
            <span className="text-xs font-medium px-2.5 py-1 bg-[#161925] border border-slate-800 rounded-full text-slate-400">
              {FOLDER_CONTENTS.length} Items
            </span>
          </div>
          
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            {FOLDER_CONTENTS.map(file => (
              <FileCard key={file.id} file={file} />
            ))}
          </div>
        </div>

        {/* Sidebar: Upload Area & Info */}
        <div className="space-y-6">
          <div className="bg-[#12141d] rounded-xl border border-slate-800/60 p-6 flex flex-col h-[300px]">
            <h3 className="text-sm font-semibold uppercase tracking-wider text-slate-400 mb-4">Upload New Assets</h3>
            
            {/* Upload Dropzone */}
            <div className="flex-1 border-2 border-dashed border-slate-700 rounded-xl flex flex-col items-center justify-center p-6 text-center hover:bg-[#161925] hover:border-indigo-500/50 transition-colors cursor-pointer group">
              <div className="w-12 h-12 rounded-full bg-slate-800 group-hover:bg-indigo-500/20 flex items-center justify-center mb-3 transition-colors">
                <UploadCloud className="w-6 h-6 text-slate-400 group-hover:text-indigo-400" />
              </div>
              <p className="text-sm font-medium text-slate-200 mb-1">Click to upload or drag & drop</p>
              <p className="text-xs text-slate-500">SVG, PNG, JPG or PDF (max. 800x400px)</p>
              
              <button className="mt-4 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium rounded-lg transition-colors flex items-center gap-2">
                <Plus className="w-4 h-4" /> Browse Files
              </button>
            </div>
          </div>

          {/* Folder Meta Info (Optional visual filler) */}
          <div className="bg-[#12141d] rounded-xl border border-slate-800/60 p-5 space-y-4">
            <h3 className="text-sm font-semibold uppercase tracking-wider text-slate-400">Folder Details</h3>
            <div className="space-y-3">
              <div className="flex justify-between text-sm">
                <span className="text-slate-500">Client</span>
                <span className="text-slate-200 font-medium">{folder.client}</span>
              </div>
              <div className="flex justify-between text-sm">
                <span className="text-slate-500">Last Modified</span>
                <span className="text-slate-200 font-medium">{folder.updated}</span>
              </div>
              <div className="flex justify-between text-sm">
                <span className="text-slate-500">Access Level</span>
                <span className="text-slate-200 font-medium flex items-center gap-1">
                  <div className="w-2 h-2 rounded-full bg-emerald-500"></div> Full Read/Write
                </span>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  );
}

// --- REUSABLE UI ELEMENTS ---

function FolderCard({ folder, onClick }) {
  return (
    <div 
      onClick={onClick}
      className={`bg-[#161925] border border-slate-800 rounded-xl p-5 hover:border-indigo-500/50 hover:bg-[#1a1d27] transition-all cursor-pointer group relative overflow-hidden`}
    >
      {/* Decorative top border matched to specific folder color if needed, or uniform */}
      <div className={`absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-slate-800 to-transparent group-hover:from-indigo-500 transition-colors`}></div>
      
      <div className="flex justify-between items-start mb-4">
        <div className="w-10 h-10 rounded-lg bg-[#1e2235] flex items-center justify-center text-indigo-400 group-hover:scale-110 transition-transform">
          <Folder className="w-5 h-5" />
        </div>
        <button className="text-slate-500 hover:text-white p-1">
          <MoreVertical className="w-4 h-4" />
        </button>
      </div>
      
      <h3 className="text-lg font-semibold text-white mb-1 group-hover:text-indigo-300 transition-colors">{folder.name}</h3>
      <p className="text-sm text-slate-400 mb-4">{folder.client}</p>
      
      <div className="flex items-center justify-between text-xs font-medium text-slate-500 pt-4 border-t border-slate-800/60">
        <span className="bg-slate-800/50 px-2 py-1 rounded-md">{folder.files} files</span>
        <span>Updated {folder.updated}</span>
      </div>
    </div>
  );
}

function FolderListItem({ folder, onClick }) {
  return (
    <div 
      onClick={onClick}
      className="flex items-center justify-between p-4 bg-[#161925] border border-slate-800 rounded-xl hover:border-indigo-500/50 hover:bg-[#1a1d27] transition-all cursor-pointer group"
    >
      <div className="flex items-center gap-4 flex-1">
        <div className="w-10 h-10 rounded-lg bg-[#1e2235] flex items-center justify-center text-indigo-400">
          <Folder className="w-5 h-5" />
        </div>
        <div>
          <h3 className="text-base font-semibold text-white group-hover:text-indigo-300 transition-colors">{folder.name}</h3>
          <p className="text-xs text-slate-400">{folder.client}</p>
        </div>
      </div>
      
      <div className="flex items-center gap-8 text-sm text-slate-500">
        <div className="hidden sm:block w-24 text-right">{folder.files} files</div>
        <div className="hidden md:block w-32 text-right">{folder.updated}</div>
        <button className="p-2 text-slate-400 hover:text-white">
          <MoreVertical className="w-4 h-4" />
        </button>
      </div>
    </div>
  );
}

function FileCard({ file }) {
  // Determine styling based on file type to match image 4's distinct card styles
  let Icon = FileText;
  let tagColor = 'bg-slate-800 text-slate-300';
  let iconColor = 'text-slate-400';
  let hoverBorder = 'hover:border-slate-600';

  if (file.type === 'image') {
    Icon = ImageIcon;
    tagColor = 'bg-blue-900/40 text-blue-400';
    iconColor = 'text-blue-400';
    hoverBorder = 'hover:border-blue-500/50';
  } else if (file.type === 'pdf') {
    Icon = FileText;
    tagColor = 'bg-pink-900/40 text-pink-400';
    iconColor = 'text-pink-400';
    hoverBorder = 'hover:border-pink-500/50';
  } else if (file.type === 'video') {
    Icon = Video;
    tagColor = 'bg-purple-900/40 text-purple-400';
    iconColor = 'text-purple-400';
    hoverBorder = 'hover:border-purple-500/50';
  }

  return (
    <div className={`bg-[#12141d] border border-slate-800/80 rounded-xl p-5 flex flex-col ${hoverBorder} hover:bg-[#161925] transition-all group`}>
      {/* Top Tag */}
      <div className="flex justify-between items-start mb-6">
        <span className={`text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded ${tagColor}`}>
          {file.type}
        </span>
        <button className="text-slate-500 opacity-0 group-hover:opacity-100 transition-opacity hover:text-white">
           <MoreVertical className="w-4 h-4" />
        </button>
      </div>

      {/* Centered Icon representing preview */}
      <div className="flex-1 flex items-center justify-center py-4 mb-4">
        <div className="w-16 h-16 rounded-2xl bg-slate-800/50 flex items-center justify-center transform group-hover:scale-110 transition-transform duration-300">
          <Icon className={`w-8 h-8 ${iconColor}`} />
        </div>
      </div>

      {/* File Info Footer */}
      <div className="mt-auto border-t border-slate-800/60 pt-4">
        <p className="text-sm font-medium text-slate-200 truncate" title={file.name}>
          {file.name}
        </p>
        <div className="flex justify-between items-center mt-1">
          <span className="text-[10px] text-slate-500">{file.date}</span>
          <span className="text-[10px] uppercase font-medium text-slate-600">{file.size}</span>
        </div>
      </div>
    </div>
  );
}

function TasksView({ onOpenFolder }) {
  const getTagStyles = (tag) => {
    switch(tag) {
      case 'PENDING': return 'bg-slate-800 text-slate-300 border border-slate-700';
      case 'IN PROGRESS': return 'bg-indigo-900/30 text-indigo-400 border border-indigo-800/50';
      case 'DONE': return 'bg-emerald-900/30 text-emerald-400 border border-emerald-800/50';
      case 'NEEDS ATTENTION': return 'bg-red-900/20 text-red-400 border border-red-900/50';
      default: return 'bg-slate-800 text-slate-300';
    }
  };

  return (
    <div className="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
      <div className="flex justify-between items-end mb-6">
        <div>
          <h3 className="text-xs font-bold uppercase tracking-widest text-indigo-400 mb-2">Production Queue</h3>
          <h2 className="text-3xl font-bold text-white">All tasks</h2>
          <p className="text-sm text-slate-400 mt-2">Fresh queue items that need attention, assignment, or review.</p>
        </div>
        <div className="bg-[#161925] border border-slate-800 rounded-full px-4 py-1.5 text-xs font-semibold text-slate-400 hidden sm:block">
          {ALL_TASKS.length} ENTRIES
        </div>
      </div>

      <div className="bg-[#12141d] border border-slate-800 rounded-xl overflow-hidden shadow-xl shadow-black/20">
        {/* Table Header */}
        <div className="hidden lg:grid grid-cols-12 gap-4 p-4 border-b border-slate-800 text-xs font-bold text-slate-500 uppercase tracking-wider bg-[#161925]/50">
          <div className="col-span-4">Request</div>
          <div className="col-span-2">Client</div>
          <div className="col-span-2">Type</div>
          <div className="col-span-2">Due Date</div>
          <div className="col-span-1 text-center">State</div>
          <div className="col-span-1 text-right">Action</div>
        </div>

        {/* Task Rows */}
        <div className="divide-y divide-slate-800/80">
          {ALL_TASKS.map((task) => (
             <div key={task.id} className="grid grid-cols-1 lg:grid-cols-12 gap-4 p-5 lg:p-4 items-center hover:bg-[#161925] transition-colors group">
                <div className="col-span-4 space-y-3">
                   <div className="flex flex-wrap items-center gap-2">
                     <span className="text-xs font-mono text-slate-500">{task.id}</span>
                     {task.tags.map(tag => (
                       <span key={tag} className={`text-[9px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider ${getTagStyles(tag)}`}>
                         {tag}
                       </span>
                     ))}
                   </div>
                   <div>
                     <h4 className="text-base font-semibold text-slate-200 group-hover:text-indigo-300 transition-colors">{task.title}</h4>
                     <p className="text-xs text-slate-500 mt-1">
                       ASSIGNED FOLDER <span className="text-slate-400 font-medium ml-1">{task.folder}</span>
                     </p>
                   </div>
                </div>

                <div className="col-span-2 text-sm font-medium text-slate-300 lg:block flex justify-between items-center">
                  <span className="lg:hidden text-xs text-slate-500 uppercase">Client</span>
                  {task.client}
                </div>

                <div className="col-span-2 text-sm text-slate-400 lg:block flex justify-between items-center">
                  <span className="lg:hidden text-xs text-slate-500 uppercase">Type</span>
                  {task.type}
                </div>

                <div className="col-span-2 text-sm text-slate-300 flex justify-between items-center lg:justify-start lg:gap-2">
                  <span className="lg:hidden text-xs text-slate-500 uppercase">Due</span>
                  <div className="flex items-center gap-2">
                    <Clock className="w-4 h-4 text-slate-500" />
                    {task.dueDate}
                  </div>
                </div>

                <div className="col-span-1 flex lg:justify-center justify-between items-center">
                   <span className="lg:hidden text-xs text-slate-500 uppercase">State</span>
                   <span className="text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider bg-[#1a1d27] text-slate-400 border border-slate-700">
                     {task.status}
                   </span>
                </div>

                <div className="col-span-1 flex lg:justify-end justify-start mt-2 lg:mt-0">
                  <button className="w-full lg:w-auto px-4 py-2.5 lg:py-2 bg-indigo-600/10 text-indigo-400 hover:bg-indigo-600 hover:text-white border border-indigo-600/50 rounded-lg text-xs font-bold tracking-wider transition-colors whitespace-nowrap">
                    {task.actionText}
                  </button>
                </div>
             </div>
          ))}
        </div>
      </div>
    </div>
  );
}

function TaskModal({ task, onClose, onUpdateStatus }) {
  const [isStatusDropdownOpen, setIsStatusDropdownOpen] = useState(false);
  const statuses = ['Pending', 'In Progress', 'Done'];

  const getStatusColor = (status) => {
    switch(status) {
      case 'Done': return 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30';
      case 'In Progress': return 'bg-indigo-500/20 text-indigo-400 border-indigo-500/30';
      default: return 'bg-slate-800 text-slate-300 border-slate-700';
    }
  };

  const handleStatusSelect = (status) => {
    onUpdateStatus(status);
    setIsStatusDropdownOpen(false);
  };

  return (
    <div className="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6 animate-in fade-in duration-200">
      <div 
        className="absolute inset-0 bg-black/60 backdrop-blur-sm"
        onClick={onClose}
      ></div>
      
      <div className="relative w-full max-w-xl bg-[#12141d] border border-slate-800 rounded-2xl shadow-2xl overflow-visible flex flex-col animate-in zoom-in-95 duration-200">
        
        {/* Modal Header */}
        <div className="flex items-center justify-between px-6 py-4 border-b border-slate-800 bg-[#161925]/50">
          <div className="flex items-center gap-3">
            <span className="px-2.5 py-0.5 rounded-full bg-slate-800 text-slate-300 text-xs font-semibold uppercase tracking-wider border border-slate-700">
              {task.id}
            </span>
            <span className="text-sm font-medium text-slate-400">{task.client}</span>
          </div>
          <button 
            onClick={onClose}
            className="p-1 text-slate-500 hover:text-white hover:bg-slate-800 rounded-lg transition-colors"
          >
            <X className="w-5 h-5" />
          </button>
        </div>

        {/* Modal Body */}
        <div className="p-6 space-y-6">
          <div>
            <h2 className="text-2xl font-bold text-white mb-3">{task.title}</h2>
            <p className="text-slate-400 text-sm leading-relaxed">
              {task.description}
            </p>
          </div>

          <div className="flex items-center gap-6 py-4 border-y border-slate-800/60">
            <div>
              <p className="text-[10px] uppercase tracking-wider text-slate-500 font-semibold mb-1">Due Date</p>
              <div className="flex items-center gap-2 text-sm text-slate-200 font-medium">
                <Clock className="w-4 h-4 text-indigo-400" />
                {task.dueDate}
              </div>
            </div>
            <div>
              <p className="text-[10px] uppercase tracking-wider text-slate-500 font-semibold mb-1">Priority</p>
              <span className={`text-xs font-bold px-2 py-0.5 rounded uppercase tracking-wider ${task.priority === 'High' ? 'bg-red-900/30 text-red-400' : 'bg-amber-900/30 text-amber-400'}`}>
                {task.priority}
              </span>
            </div>
          </div>

          {/* Status Update Dropdown */}
          <div className="space-y-3 pb-8">
            <p className="text-[10px] uppercase tracking-wider text-slate-500 font-semibold">Update Status</p>
            
            <div className="relative w-48">
              <button 
                onClick={() => setIsStatusDropdownOpen(!isStatusDropdownOpen)}
                className={`w-full py-2.5 px-4 rounded-lg text-sm font-semibold transition-all flex items-center justify-between border ${getStatusColor(task.status)}`}
              >
                <span>{task.status}</span>
                <ChevronDown className={`w-4 h-4 transition-transform ${isStatusDropdownOpen ? 'rotate-180' : ''}`} />
              </button>

              {isStatusDropdownOpen && (
                <div className="absolute top-full left-0 mt-2 w-full bg-[#1a1d27] border border-slate-700 rounded-lg shadow-xl overflow-hidden z-50 animate-in fade-in slide-in-from-top-2 duration-200">
                  {statuses.map((status) => (
                    <button
                      key={status}
                      onClick={() => handleStatusSelect(status)}
                      className="w-full text-left px-4 py-3 text-sm text-slate-300 hover:bg-[#252a3a] hover:text-white transition-colors flex items-center justify-between border-b border-slate-800/50 last:border-0"
                    >
                      {status}
                      {task.status === status && <CheckCircle className="w-4 h-4 text-indigo-400" />}
                    </button>
                  ))}
                </div>
              )}
            </div>

          </div>
        </div>

      </div>
    </div>
  );
}