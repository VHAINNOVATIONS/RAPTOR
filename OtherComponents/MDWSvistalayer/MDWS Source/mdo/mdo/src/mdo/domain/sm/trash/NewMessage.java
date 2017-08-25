package gov.va.med.mhv.sm.model;

import gov.va.med.mhv.sm.enumeration.MessageCategoryTypeEnum;

import java.io.Serializable;



/**
 * 
 * This is used as a DTO for sending messages.
 * 
 * Because of the data model the Message object is
 * not as useful because the subject, to, and from
 * members are separated into different objects.
 * 
 * This is a useful way for the web tier to package up
 * Messages into a simple object for processing
 * 
 * 
 * @author vhalommccarw
 *
 */



public class NewMessage implements Serializable {

	/**
	 * 
	 */
	private static final long serialVersionUID = -8542725951535311517L;
	private MailParticipant from;
	private MailParticipant to;
	private MailParticipant cc;
	private String subject;
	private String body;
	private TriageGroup triageGroup;
	private boolean draft;
	private Long attachmentId;
	private String attachmentName;
	private Long messageCategoryTypeId;
	
	
	public MailParticipant getFrom() {
		return from;
	}
	public void setFrom(MailParticipant from) {
		this.from = from;
	}
	public MailParticipant getTo() {
		return to;
	}
	public void setTo(MailParticipant to) {
		this.to = to;
	}
	public String getSubject() {
		return subject;
	}
	public void setSubject(String subject) {
		this.subject = subject;
	}
	public String getBody() {
		return body;
	}
	public void setBody(String body) {
		this.body = body;
	}
	public TriageGroup getTriageGroup() {
		return triageGroup;
	}
	public void setTriageGroup(TriageGroup triageGroup) {
		this.triageGroup = triageGroup;
	}
	public boolean isDraft() {
		return draft;
	}
	public void setDraft(boolean draft) {
		this.draft = draft;
	}
	public Long getAttachmentId() {
		return attachmentId;
	}
	public void setAttachmentId(Long attachmentId) {
		this.attachmentId = attachmentId;
	}
	public Long getMessageCategoryTypeId() {
		return messageCategoryTypeId;
	}
	public void setMessageCategoryTypeId(Long messageCategoryTypeId) {
		this.messageCategoryTypeId = messageCategoryTypeId;
	}
	
	public MailParticipant getCc() {
		return cc;
	}
	public void setCc(MailParticipant cc) {
		this.cc = cc;
	}
	public String getAttachmentName() {
		return attachmentName;
	}
	public void setAttachmentName(String attachmentName) {
		this.attachmentName = attachmentName;
	}
}
