package gov.va.med.mhv.sm.model;

import java.io.Serializable;
import java.util.Date;
import java.util.List;

import gov.va.med.mhv.sm.enumeration.ParticipantTypeEnum;
import gov.va.med.mhv.sm.enumeration.UserTypeEnum;

/**
 * 
 * @author vhalommccarw
 *
 *  represents a specific actor of patient
 *  
 */
public class Patient extends User implements Serializable{

	
	/**
	 * 
	 */
	private static final long serialVersionUID = -8448442975344919006L;
	protected Date dob;
	protected String icn; 
	protected List<PatientFacility> facilities;	
	protected Date relationshipUpdate;

	public Patient(){
		super();
		this.userType = UserTypeEnum.PATIENT;
		this.participantType = ParticipantTypeEnum.PATIENT;
	}

	public Date getDob() {
		return dob;
	}
	public void setDob(Date dob) {
		this.dob = dob;
	}
	public String getIcn() {
		return icn;
	}
	public void setIcn(String icn) {
		this.icn = icn;
	}
	public List<PatientFacility> getFacilities() {
		return facilities;
	}
	public void setFacilities(List<PatientFacility> facilities) {
		this.facilities = facilities;
	}
	public Date getRelationshipUpdate() {
		return relationshipUpdate;
	}
	public void setRelationshipUpdate(Date relationshipUpdate) {
		this.relationshipUpdate = relationshipUpdate;
	}

}
