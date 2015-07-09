package gov.va.med.mhv.sm.model;

import java.io.Serializable;
import java.util.Date;
import java.util.List;

import org.apache.commons.logging.Log;
import org.apache.commons.logging.LogFactory;

import gov.va.med.mhv.sm.enumeration.ParticipantTypeEnum;
import gov.va.med.mhv.sm.enumeration.UserTypeEnum;


/**
 * 
 * @author vhalommccarw
 *
 *  represents a specific actor of provider
 *  
 */
public class Clinician extends User implements Serializable{

	/**
	 * 
	 */
	private static final long serialVersionUID = -7110051897149899782L;

	@SuppressWarnings("unused")
	private static final Log log = LogFactory.getLog(Clinician.class);
	
	private String stationNo;
	private String duz;
	//list of distribution groups that the actor belongs 
	protected List<DistributionGroup> distGroups;
	// this determines if the user can create/sign TIU progress notes
	protected boolean provider;
	
	private ClinicalUserType clinicalUserType;
	
	public Clinician(){
		super();
		this.userType = UserTypeEnum.CLINICIAN;
		this.participantType = ParticipantTypeEnum.CLINICIAN;
	}
	
	public String getStationNo() {
		return stationNo;
	}
	public void setStationNo(String stationNo) {
		this.stationNo = stationNo;
	}
	public String getDuz() {
		return duz;
	}
	public void setDuz(String duz) {
		this.duz = duz;
	}
	public List<DistributionGroup> getDistGroups() {
		return distGroups;
	}
	public void setDistGroups(List<DistributionGroup> distGroups) {
		this.distGroups = distGroups;
	}
	public boolean isProvider() {
		return provider;
	}
	public void setProvider(boolean isProvider) {
		this.provider = isProvider;
	}
	
		public ClinicalUserType getClinicalUserType() {
		return clinicalUserType;
	}
	public void setClinicalUserType(ClinicalUserType clinicalUserType) {
		this.clinicalUserType = clinicalUserType;
	}
	
	
	public boolean equals(Object a){
		try{
			Clinician x = (Clinician)a;
			if(this.getId().equals(x.getId())) 
				return true;
			return false;
		}catch(Exception e){
			return false;
		}
	}

	public int hashCode(){
		return this.getId().hashCode();
	}
	
}
