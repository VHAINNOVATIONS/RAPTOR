package gov.va.med.mhv.sm.model;

import java.util.ArrayList;
import java.util.Date;
import java.util.List;

public class TiuNotePreview {
	
	private boolean isLocked;
	private Patient patient;
	private Date createdDate;
	private String facilityName;
	private boolean isAddendum;
	private String existingNote;
	private String proposedNote;
	private List<Message> existingMessages;
	private List<Message> proposedMessages;
	
	public TiuNotePreview(){
		existingMessages = new ArrayList<Message>();
		proposedMessages = new ArrayList<Message>();
	}
	
	public Patient getPatient() {
		return patient;
	}
	public void setPatient(Patient patient) {
		this.patient = patient;
	}
	public Date getCreatedDate() {
		return createdDate;
	}
	public void setCreatedDate(Date createdDate) {
		this.createdDate = createdDate;
	}
	public String getFacilityName() {
		return facilityName;
	}
	public void setFacilityName(String facilityName) {
		this.facilityName = facilityName;
	}
	public boolean isAddendum() {
		return isAddendum;
	}
	public void setAddendum(boolean isAddendum) {
		this.isAddendum = isAddendum;
	}
	public String getExistingNote() {
		return existingNote;
	}
	public void setExistingNote(String existingNote) {
		this.existingNote = existingNote;
	}
	public String getProposedNote() {
		return proposedNote;
	}
	public void setProposedNote(String proposedNote) {
		this.proposedNote = proposedNote;
	}
	public List<Message> getExistingMessages() {
		return existingMessages;
	}
	public void setExistingMessages(List<Message> existingMessages) {
		this.existingMessages = existingMessages;
	}
	public List<Message> getProposedMessages() {
		return proposedMessages;
	}
	public void setProposedMessages(List<Message> proposedMessages) {
		this.proposedMessages = proposedMessages;
	}
	public boolean isLocked() {
		return isLocked;
	}
	public void setLocked(boolean isLocked) {
		this.isLocked = isLocked;
	}
	
	
}
