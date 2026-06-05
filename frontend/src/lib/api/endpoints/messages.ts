import { api } from "@/lib/api/client";
import type { Conversation, Message } from "@/types/api";

export const messageApi = {
  conversations: () => api.list<Conversation>("/conversations"),

  startConversation: (data: { project_id?: string; freelance_id?: string; client_id?: string; subject?: string }) =>
    api.post<Conversation>("/conversations", data),

  messages: (conversationId: string) =>
    api.list<Message>(`/conversations/${conversationId}`),

  sendMessage: (conversationId: string, data: { content: string }) =>
    api.post<Message>(`/conversations/${conversationId}/messages`, data),

  markRead: (messageId: string) =>
    api.put(`/messages/${messageId}/read`),
};
