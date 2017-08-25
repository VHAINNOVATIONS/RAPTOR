package gov.va.med.mhv.sm.model;

import java.io.Serializable;

public class PatientTriageMap extends BaseModel implements Serializable {

	
	/**
	 * 
	 */
	private static final long serialVersionUID = -4585150110378022297L;
	private TriageRelation triageRelation;
	private Patient patient;
	
	
	public TriageRelation getTriageRelation() {
		return triageRelation;
	}
	public void setTriageRelation(TriageRelation triageRelation) {
		this.triageRelation = triageRelation;
	}
	public Patient getPatient() {
		return patient;
	}
	public void setPatient(Patient patient) {
		this.patient = patient;
	}
	
	
}
