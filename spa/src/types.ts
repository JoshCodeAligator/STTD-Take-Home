export type Ticket = {
    id: number;
    subject: string;
    description: string;
    requester_email: string;
    status: 'new'|'open'|'closed';
    ai_category?: string|null;
    ai_confidence?: number|null;
    override_category?: string|null;
    effective_category: string;
    classification_status: 'idle'|'queued'|'running'|'done'|'failed';
    notes_count?: number;
    created_at: string;
  };
  
  export type Note = {
    id: number;
    ticket_id: number;
    author: string;
    body: string;
    created_at: string;
  };
  
  export type Paginated<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    total: number;
  };
  