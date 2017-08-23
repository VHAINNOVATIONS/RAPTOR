package gov.va.med.mhv.sm.model;

import gov.va.med.mhv.sm.enumeration.ParticipantTypeEnum;

public interface MailParticipant {

	public ParticipantTypeEnum getParticipantType();
	public String getName();
	public Long getId();
}
