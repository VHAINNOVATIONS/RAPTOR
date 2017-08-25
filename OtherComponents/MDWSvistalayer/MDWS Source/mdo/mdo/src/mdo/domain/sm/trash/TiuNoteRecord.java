package gov.va.med.mhv.sm.model;

import java.io.Serializable;
import java.util.Date;

public class TiuNoteRecord extends BaseModel implements Serializable{

	/**
	 * 
	 */
	private static final long serialVersionUID = 615672084864146276L;
	private String vistaDiv;
	private Long threadId; 
	private Long lastMessageId;
	private Date lockedDate;
	private String conversationId;
	private Date noteCreationDate;
	private String comments;
	
	public TiuNoteRecord(){
		noteCreationDate = new Date();
		threadId = 0L;
		lastMessageId = 0L;
	}
	
	public String getVistaDiv() {
		return vistaDiv;
	}
	public void setVistaDiv(String vistaDiv) {
		this.vistaDiv = vistaDiv;
	}
	public Long getThreadId() {
		return threadId;
	}
	public void setThreadId(Long threadId) {
		this.threadId = threadId;
	}
	public Long getLastMessageId() {
		return lastMessageId;
	}
	public void setLastMessageId(Long lastMessageId) {
		this.lastMessageId = lastMessageId;
	}
	public Date getLockedDate() {
		return lockedDate;
	}
	public void setLockedDate(Date lockedDate) {
		this.lockedDate = lockedDate;
	}
	public String getConversationId() {
		return conversationId;
	}
	public void setConversationId(String conversationId) {
		this.conversationId = conversationId;
	}
	public Date getNoteCreationDate() {
		return noteCreationDate;
	}
	public void setNoteCreationDate(Date noteCreationDate) {
		this.noteCreationDate = noteCreationDate;
	}
	public String getComments() {
		return comments;
	}
	public void setComments(String comments) {
		this.comments = comments;
	}

}
