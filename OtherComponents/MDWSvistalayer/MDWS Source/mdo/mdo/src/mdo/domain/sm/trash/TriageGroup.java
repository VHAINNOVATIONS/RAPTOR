package gov.va.med.mhv.sm.model;

import gov.va.med.mhv.sm.enumeration.ParticipantTypeEnum;

import java.io.Serializable;
import java.util.ArrayList;
import java.util.List;

/**
 * 
 * @author vhalommccarw
 *
 *  a MailGroup represents a "triage group" consisting of
 *  on or more providers.  
 *  
 *  The members are a group of actors that will be understood
 *  to be Providers for the foreseeable future but not coded
 *  as such for flexibility.
 *
 */
public class TriageGroup extends BaseModel implements Serializable, MailParticipant, Comparable<TriageGroup> {

	/**
	 * 
	 */
	private static final long serialVersionUID = -496165267472234346L;
	private String name;
	private String description;
	private List<Clinician> clinicians = new ArrayList<Clinician>();
	private List<Patient> patients = new ArrayList<Patient>();
	private List<TriageRelation> relations = new ArrayList<TriageRelation>();
	private String vistaDiv;
	
	public String getDescription() {
		return description;
	}
	public void setDescription(String description) {
		this.description = description;
	}
	public String getName() {
		return name;
	}
	public void setName(String name) {
		this.name = name;
	}
	public List<Clinician> getClinicians() {
		return clinicians;
	}
	public void setClinicians(List<Clinician> clinicians) {
		this.clinicians = clinicians;
	}
	public List<Patient> getPatients() {
		return patients;
	}
	public void setPatients(List<Patient> patients) {
		this.patients = patients;
	}
	public List<TriageRelation> getRelations() {
		return relations;
	}
	public void setRelations(List<TriageRelation> relations) {
		this.relations = relations;
	}
	public void addRelation(TriageRelation tr) {
		tr.setTriageGroup(this);
		getRelations().add(tr);
	}
	public void removeRelation(TriageRelation tr) {
		tr.setTriageGroup(null);
		getRelations().remove(tr);
	}
	public ParticipantTypeEnum getParticipantType(){
		return ParticipantTypeEnum.TRIAGE_GROUP;
	}
	public String getVistaDiv() {
		return vistaDiv;
	}
	public void setVistaDiv(String vistaDiv) {
		this.vistaDiv = vistaDiv;
	}
	
	public String toString(){
		return getId() + "^" + name;
	}
	
	public int compareTo(TriageGroup other) {
		return this.getName().compareTo(other.getName());
	}
}
