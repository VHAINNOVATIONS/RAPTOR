package gov.va.med.mhv.sm.model;

import java.io.Serializable;
import java.util.Date;
import java.util.List;

import org.apache.commons.logging.Log;
import org.apache.commons.logging.LogFactory;

import gov.va.med.mhv.sm.enumeration.ClinicianStatusEnum;
import gov.va.med.mhv.sm.enumeration.ParticipantTypeEnum;


/** 
 * Represents an actual message.  
 * 
 * @author vhalommccarw
 */


public class Message extends BaseModel implements Serializable{

	/**
	 * 
	 */
	private static final long serialVersionUID = 7524094007509849830L;

	@SuppressWarnings("unused")
	private static final Log log = LogFactory.getLog(Message.class);
	
	private Thread thread;
	private String body;
	private ClinicianStatusEnum status;
	private Date completedDate;
	private Clinician statusSetBy;
	private Clinician assignedTo;
	private String checksum;
	private Date sentDate;
	private Date sentDateLocal;
	private Date escalatedDate;
	private Date escalationNotificationDate;
	private Long escalationNotificationTries;
	private ParticipantTypeEnum senderType;
	private Long senderId;
	private String senderName;
	private ParticipantTypeEnum recipientType;
	private Long recipientId;
	private String recipientName;
	private Long ccRecipientId;
	private String ccRecipientName;
	private String readReceipt;
	public boolean attachment;
	private Long attachmentId;
	
	private List<Addressee> addressees;	
	
	public Message(){
		status = ClinicianStatusEnum.INCOMPLETE;		
	}
	
	public Clinician getAssignedTo() {
		return assignedTo;
	}
	public void setAssignedTo(Clinician assignedTo) {
		this.assignedTo = assignedTo;
	}
	public String getBody() {
		return body;
	}
	public void setBody(String body) {
		this.body = body;
	}
	public ClinicianStatusEnum getStatus() {
		return status;
	}
	public void setStatus(ClinicianStatusEnum status) {
		this.status = status;
	}
	public Clinician getStatusSetBy() {
		return statusSetBy;
	}
	public void setStatusSetBy(Clinician statusSetBy) {
		this.statusSetBy = statusSetBy;
	}
	public Thread getThread() {
		return thread;
	}
	public void setThread(Thread thread) {
		this.thread = thread;
	}
	public Date getCompletedDate() {
		return completedDate;
	}
	public void setCompletedDate(Date completedDate) {
		this.completedDate = completedDate;
	}
	public List<Addressee> getAddressees() {
		return addressees;
	}
	public void setAddressees(List<Addressee> addressees) {
		this.addressees = addressees;
	}
	public String getChecksum() {
		return checksum;
	}
	public void setChecksum(String checksum) {
		this.checksum = checksum;
	}
	public Date getSentDate() {
		return sentDate;
	}
	public void setSentDate(Date sentDate) {
		this.sentDate = sentDate;
	}
	public Date getSentDateLocal() {
		return sentDateLocal;
	}
	public void setSentDateLocal(Date sentDateLocal) {
		this.sentDateLocal = sentDateLocal;
	}
	public Date getEscalatedDate() {
		return escalatedDate;
	}
	public void setEscalatedDate(Date escalatedDate) {
		this.escalatedDate = escalatedDate;
	}	
	public boolean isEscalated(){
		return escalatedDate != null;
	}
	public Date getEscalationNotificationDate() {
		return escalationNotificationDate;
	}
	public void setEscalationNotificationDate(Date escalationNotificationDate) {
		this.escalationNotificationDate = escalationNotificationDate;
	}
	public Long getEscalationNotificationTries() {
		return escalationNotificationTries;
	}
	public void setEscalationNotificationTries(Long escalationNotificationTries) {
		this.escalationNotificationTries = escalationNotificationTries;
	}
	public ParticipantTypeEnum getSenderType() {
		return senderType;
	}
	public void setSenderType(ParticipantTypeEnum senderType) {
		this.senderType = senderType;
	}
	public Long getSenderId() {
		return senderId;
	}
	public void setSenderId(Long senderId) {
		this.senderId = senderId;
	}
	public String getSenderName() {
		return senderName;
	}
	public void setSenderName(String senderName) {
		this.senderName = senderName;
	}
	public ParticipantTypeEnum getRecipientType() {
		return recipientType;
	}
	public void setRecipientType(ParticipantTypeEnum recipientType) {
		this.recipientType = recipientType;
	}
	public Long getRecipientId() {
		return recipientId;
	}
	public void setRecipientId(Long recipientId) {
		this.recipientId = recipientId;
	}
	public String getRecipientName() {
		return recipientName;
	}
	public void setRecipientName(String recipientName) {
		this.recipientName = recipientName;
	}

	public String getReadReceipt() {
		return readReceipt;
	}

	public void setReadReceipt(String readReceipt) {
		this.readReceipt = readReceipt;
	}

	public boolean isAttachment() {
		return attachment;
	}

	public void setAttachment(boolean attachment) {
		this.attachment = attachment;
	}
	
	public Long getCcRecipientId() {
		return ccRecipientId;
	}

	public void setCcRecipientId(Long ccRecipientId) {
		this.ccRecipientId = ccRecipientId;
	}

	public String getCcRecipientName() {
		return ccRecipientName;
	}

	public void setCcRecipientName(String ccRecipientName) {
		this.ccRecipientName = ccRecipientName;
	}

	public Long getAttachmentId() {
		return attachmentId;
	}

	public void setAttachmentId(Long attachmentId) {
		this.attachmentId = attachmentId;
	}
}
