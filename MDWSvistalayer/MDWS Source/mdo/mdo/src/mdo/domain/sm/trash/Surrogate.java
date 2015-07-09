package gov.va.med.mhv.sm.model;

import gov.va.med.mhv.sm.enumeration.ParticipantTypeEnum;

import java.io.Serializable;
import java.util.Date;

public class Surrogate extends BaseModel implements Serializable
{

	private static final long serialVersionUID = 6814862738195064844L;
	private Long surrogateId;
	private User smsUser;
	private ParticipantTypeEnum surrogateType;
	private Date surrogateStartDate;
	private Date surrogateEndDate;
	private boolean surrogateAllDay;
	private Long timeZone;

	public User getSmsUser() {
		return smsUser;
	}

	public void setSmsUser(User smsUser) {
		this.smsUser = smsUser;
	}

	public boolean isSurrogateAllDay() {
		return surrogateAllDay;
	}

	public void setSurrogateAllDay(boolean surrogateAllDay) {
		this.surrogateAllDay = surrogateAllDay;
	}

	public Date getSurrogateEndDate() {
		return surrogateEndDate;
	}

	public void setSurrogateEndDate(Date surrogateEndDate) {
		this.surrogateEndDate = surrogateEndDate;
	}

	public Long getSurrogateId() {
		return surrogateId;
	}

	public void setSurrogateId(Long surrogateId) {
		this.surrogateId = surrogateId;
	}

	public Date getSurrogateStartDate() {
		return surrogateStartDate;
	}

	public void setSurrogateStartDate(Date surrogateStartDate) {
		this.surrogateStartDate = surrogateStartDate;
	}
	
	public boolean equals(Object a){
		try{
			Surrogate x = (Surrogate)a;
			return this.getId().equals(x.getId()); 
		}catch(Exception e){
			return false;
		}
	}
	
	public int hashCode(){
		return this.getId().hashCode();
	}

	public Long getTimeZone() {
		return timeZone;
	}

	public void setTimeZone(Long timeZone) {
		this.timeZone = timeZone;
	}

	public void setSurrogateType(ParticipantTypeEnum surrogateType) {
		this.surrogateType = surrogateType;
	}

	public ParticipantTypeEnum getSurrogateType() {
		return surrogateType;
	}
		
}
