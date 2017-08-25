package gov.va.med.mhv.sm.model;

import java.io.Serializable;

/**
 * 
 * @author vhaiswuchitr
 *
 *  This is the relationship between a TriageGroup and
 *  the clinicians.  This is how clinicians are identified
 *  to "belong" to certain TriageGroups.  
 *
 */


public class ClinicianTriageMap extends BaseModel implements Serializable{

	/**
	 * 
	 */
	private static final long serialVersionUID = 1491063143750141027L;

	private TriageGroup triageGroup;
	
	private Clinician clinician;
	
	public TriageGroup getTriageGroup() {
		return triageGroup;
	}
	public void setTriageGroup(TriageGroup triageGroup) {
		this.triageGroup = triageGroup;
	}
	public Clinician getClinician() {
		return clinician;
	}
	public void setClinician(Clinician clinician) {
		this.clinician = clinician;
	}
	
	
	
	
}
