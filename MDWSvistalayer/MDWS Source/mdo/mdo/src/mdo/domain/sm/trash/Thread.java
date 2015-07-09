package gov.va.med.mhv.sm.model;

import gov.va.med.mhv.sm.enumeration.MessageCategoryTypeEnum;

import java.io.Serializable;
import java.util.List;

/**
 * Represents a message thread.  
 * Message threads are single threaded.  
 *
 * @author vhalommccarw
 */
public class Thread extends BaseModel implements Serializable {
	
	/**
	 * 
	 */
	private static final long serialVersionUID = 5334390699462309197L;
	private String subject;
	private List<Message> messages;
	private List<Annotation> annotations;
	private TriageGroup mailGroup;
	private MessageCategoryTypeEnum messageCategoryType;
	
	public String getSubject() {
		return subject;
	}
	public void setSubject(String subject) {
		this.subject = subject;
	}
	public List<Message> getMessages() {
		return messages;
	}
	public void setMessages(List<Message> messages) {
		this.messages = messages;
	}
	public TriageGroup getMailGroup() {
		return mailGroup;
	}
	public void setMailGroup(TriageGroup mailGroup) {
		this.mailGroup = mailGroup;
	}
	public List<Annotation> getAnnotations() {
		return annotations;
	}
	public void setAnnotations(List<Annotation> annotations) {
		this.annotations = annotations;
	}
	
	public MessageCategoryTypeEnum getMessageCategoryType() {
		return messageCategoryType;
	}
	public void setMessageCategoryType(MessageCategoryTypeEnum messageCategoryType) {
		this.messageCategoryType = messageCategoryType;
	}
}
