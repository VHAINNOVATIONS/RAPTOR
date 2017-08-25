using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Collections;
using System.Text;
using System.IO;
using gov.va.medora.mdo.dao;
using gov.va.medora.mdo.dao.vista;
using gov.va.medora.utils;
using gov.va.medora.mdo.exceptions;
using gov.va.medora.mdo.src.mdo;

namespace gov.va.medora.mdo.dao.vista
{
    public class VistaVitalsDao : IVitalsDao
    {
        AbstractConnection cxn = null;

        public VistaVitalsDao(AbstractConnection cxn)
        {
            this.cxn = cxn;
        }

        // Get all vital signs for currently selected patient (RDV call)
        public VitalSignSet[] getVitalSigns()
        {
            return getVitalSigns(cxn.Pid);
        }

        // Get all vital signs for given patient (RDV call)
        public VitalSignSet[] getVitalSigns(string dfn)
        {
            MdoQuery request = buildGetVitalSignsRdvRequest(dfn);
            string response = (string)cxn.query(request);
            return toVitalSignsFromRdv(response);
        }

        // Get vital signs within time frame for currently selected patient (RDV call)
        public VitalSignSet[] getVitalSigns(string fromDate, string toDate, int maxRex)
        {
            return getVitalSigns(cxn.Pid,fromDate,toDate,maxRex);
        }

        // Get vital signs within time frame for given patient (RDV call)
        public VitalSignSet[] getVitalSigns(string dfn, string fromDate, string toDate, int maxRex)
        {
            MdoQuery request = buildGetVitalSignsRdvRequest(dfn, fromDate, toDate, maxRex);
            string response = (string)cxn.query(request);
            return toVitalSignsFromRdv(response);
        }

        internal MdoQuery buildGetVitalSignsRdvRequest(string dfn)
        {
            VistaUtils.CheckRpcParams(dfn);
            return VistaUtils.buildReportTextRequest_AllResults(dfn, "OR_VS:VITAL SIGNS~VS;ORDV04;47;");
        }

        internal MdoQuery buildGetVitalSignsRdvRequest(string dfn, string fromDate, string toDate, int maxRex)
        {
            VistaUtils.CheckRpcParams(dfn);
            return VistaUtils.buildReportTextRequest(dfn, fromDate, toDate, maxRex, "OR_VS:VITAL SIGNS~VS;ORDV04;47;");
        }

        internal VitalSignSet[] toVitalSignsFromRdv(string response)
        {
            if (response == "")
            {
                return null;
            }
            string[] lines = StringUtils.split(response, StringUtils.CRLF);
            lines = StringUtils.trimArray(lines);
            ArrayList lst = new ArrayList();
            VitalSignSet rec = null;
            VitalSign s = null;
            for (int i = 0; i < lines.Length; i++)
            {
                string[] flds = StringUtils.split(lines[i], StringUtils.CARET);
                int fldnum = Convert.ToInt16(flds[0]);
                switch (fldnum)
                {
                    case 1:
                        if (rec != null)
                        {
                            lst.Add(rec);
                        }
                        rec = new VitalSignSet();
                        string[] subflds = StringUtils.split(flds[1], StringUtils.SEMICOLON);
                        if (subflds.Length == 1)
                        {
                            rec.Facility = new SiteId("200", subflds[0]);
                        }
                        else
                        {
                            rec.Facility = new SiteId(subflds[1], subflds[0]);
                        }
                        break;
                    case 2:
                        if (flds.Length == 2)
                        {
                            rec.Timestamp = VistaTimestamp.toUtcFromRdv(flds[1]);
                        }
                        break;
                    case 3:
                        s = new VitalSign();
                        s.Type = new ObservationType("", VitalSign.VITAL_SIGN, VitalSign.TEMPERATURE);
                        if (flds.Length == 2)
                        {
                            s.Value1 = flds[1];
                        }
                        rec.addVitalSign(VitalSign.TEMPERATURE, s);
                        break;
                    case 4:
                        s = new VitalSign();
                        s.Type = new ObservationType("", VitalSign.VITAL_SIGN, VitalSign.PULSE);
                        if (flds.Length == 2)
                        {
                            s.Value1 = flds[1];
                        }
                        rec.addVitalSign(VitalSign.PULSE, s);
                        break;
                    case 5:
                        s = new VitalSign();
                        s.Type = new ObservationType("", VitalSign.VITAL_SIGN, VitalSign.RESPIRATION);
                        if (flds.Length == 2)
                        {
                            s.Value1 = flds[1];
                        }
                        rec.addVitalSign(VitalSign.RESPIRATION, s);
                        break;
                    case 6:
                        s = new VitalSign();
                        s.Type = new ObservationType("", VitalSign.VITAL_SIGN, VitalSign.BLOOD_PRESSURE);
                        if (flds.Length == 2)
                        {
                            s.Value1 = flds[1];
                        }
                        rec.addVitalSign(VitalSign.BLOOD_PRESSURE, s);
                        break;
                    case 7:
                        s = new VitalSign();
                        s.Type = new ObservationType("", VitalSign.VITAL_SIGN, VitalSign.HEIGHT);
                        if (flds.Length == 2)
                        {
                            s.Value1 = flds[1];
                        }
                        rec.addVitalSign(VitalSign.HEIGHT, s);
                        break;
                    case 8:
                        s = new VitalSign();
                        s.Type = new ObservationType("", VitalSign.VITAL_SIGN, VitalSign.WEIGHT);
                        if (flds.Length == 2)
                        {
                            s.Value1 = flds[1];
                        }
                        rec.addVitalSign(VitalSign.WEIGHT, s);
                        break;
                    case 9:
                        s = new VitalSign();
                        s.Type = new ObservationType("", VitalSign.VITAL_SIGN, VitalSign.PAIN);
                        if (flds.Length == 2)
                        {
                            s.Value1 = flds[1];
                        }
                        rec.addVitalSign(VitalSign.PAIN, s);
                        break;
                    case 10:
                        s = new VitalSign();
                        s.Type = new ObservationType("", VitalSign.VITAL_SIGN, VitalSign.PULSE_OXYMETRY);
                        if (flds.Length == 2)
                        {
                            s.Value1 = flds[1];
                        }
                        rec.addVitalSign(VitalSign.PULSE_OXYMETRY, s);
                        break;
                    case 11:
                        s = new VitalSign();
                        s.Type = new ObservationType("", VitalSign.VITAL_SIGN, VitalSign.CENTRAL_VENOUS_PRESSURE);
                        if (flds.Length == 2)
                        {
                            s.Value1 = flds[1];
                        }
                        rec.addVitalSign(VitalSign.CENTRAL_VENOUS_PRESSURE, s);
                        break;
                    case 12:
                        s = new VitalSign();
                        s.Type = new ObservationType("", VitalSign.VITAL_SIGN, VitalSign.CIRCUMFERENCE_GIRTH);
                        if (flds.Length == 2)
                        {
                            s.Value1 = flds[1];
                        }
                        rec.addVitalSign(VitalSign.CIRCUMFERENCE_GIRTH, s);
                        break;
                    case 15:
                        if (flds.Length == 2)
                        {
                            setVitalSignQualifierStrings(rec, flds[1], "Qualifiers");
                            rec.Qualifiers = flds[1];
                        }
                        break;
                    case 16:
                        s = new VitalSign();
                        s.Type = new ObservationType("", VitalSign.VITAL_SIGN, VitalSign.BODY_MASS_INDEX);
                        if (flds.Length == 2)
                        {
                            s.Value1 = flds[1];
                        }
                        rec.addVitalSign(VitalSign.BODY_MASS_INDEX, s);
                        break;
                    case 17:
                        if (flds.Length == 2)
                        {
                            setVitalSignQualifierStrings(rec, flds[1], "Units");
                            rec.Units = flds[1];
                        }
                        break;
                    default:
                        break;
                }
            }
            lst.Add(rec);
            return (VitalSignSet[])lst.ToArray(typeof(VitalSignSet));
        }
              
        public VitalSign[] getLatestVitalSigns()
        {
            return getLatestVitalSigns(cxn.Pid);
        }

        public VitalSign[] getLatestVitalSigns(string dfn)
        {
            MdoQuery request = buildGetLatestVitalSignsRequest(dfn);
            string response = (string)cxn.query(request);
            return toLatestVitalSigns(response);
        }

        internal MdoQuery buildGetLatestVitalSignsRequest(string dfn)
        {
            VistaUtils.CheckRpcParams(dfn);
            VistaQuery vq = new VistaQuery("ORQQVI VITALS");
            vq.addParameter(vq.LITERAL, dfn);
            return vq;
        }

        internal VitalSign[] toLatestVitalSigns(string response)
        {
            if (!cxn.IsConnected)
            {
                throw new NotConnectedException();
            }

            if (response == "")
            {
                return null;
            }
            string[] lines = StringUtils.split(response, StringUtils.CRLF);
            ArrayList lst = new ArrayList(lines.Length);
            string category = "Vital Signs";
            for (int i = 0; i < lines.Length; i++)
            {
                if (lines[i] == "")
                {
                    continue;
                }
                string[] flds = StringUtils.split(lines[i], StringUtils.CARET);
                ObservationType observationType = null;
                if (flds[1] == "T")
                {
                    observationType = new ObservationType(flds[0], category, "Temperature");
                }
                else if (flds[1] == "P")
                {
                    observationType = new ObservationType(flds[0], category, "Pulse");
                }
                else if (flds[1] == "R")
                {
                    observationType = new ObservationType(flds[0], category, "Respiration");
                }
                else if (flds[1] == "BP")
                {
                    observationType = new ObservationType(flds[0], category, "Blood Pressure");
                }
                else if (flds[1] == "HT")
                {
                    observationType = new ObservationType(flds[0], category, "Height");
                }
                else if (flds[1] == "WT")
                {
                    observationType = new ObservationType(flds[0], category, "Weight");
                }
                else if (flds[1] == "PN")
                {
                    observationType = new ObservationType(flds[0], category, "Pain");
                }
                if (observationType == null)
                {
                    continue;
                }
                VitalSign observation = new VitalSign();
                observation.Type = observationType;
                observation.Value1 = flds[4];
                if (flds.Length == 6)
                {
                    observation.Value2 = flds[5];
                }
                observation.Timestamp = VistaTimestamp.toUtcString(flds[3]);
                lst.Add(observation);
            }
            return (VitalSign[])lst.ToArray(typeof(VitalSign));
        }

        internal string getVitalSignQualifierItem(string qualifiers, string key)
        {
            int p1 = qualifiers.IndexOf(key + ':');
            if (p1 == -1)
            {
                return "";
            }
            p1 += key.Length + 1;
            int p2 = p1;
            while (p2 < qualifiers.Length && qualifiers[p2] != ':')
            {
                p2++;
            }
            if (p2 < qualifiers.Length)
            {
                while (qualifiers[p2] != ',')
                {
                    p2--;
                }
            }
            return qualifiers.Substring(p1, p2 - p1).Trim();
        }

        internal void setVitalSignQualifierStrings(VitalSignSet set, string s, string qualifier)
        {
            string[] keys = new string[]
            {
                "TEMP","PULSE","BP","RESP","WT","HT","PAIN","O2","CG","CVP","BMI"
            };
            for (int i = 0; i < keys.Length; i++)
            {
                string value = getVitalSignQualifierItem(s, keys[i]);
                if (!String.IsNullOrEmpty(value))
                {
                    VitalSign theSign = getSignFromSet(set, keys[i]);
                    if (theSign == null)
                    {
                        continue;
                    }
                    if (String.Equals(qualifier, "Units", StringComparison.CurrentCultureIgnoreCase))
                    {
                        theSign.Units = value;
                        if (keys[i] == "BP")
                        {
                            theSign = set.getVitalSign(VitalSign.SYSTOLIC_BP);
                            if (theSign != null)
                            {
                                theSign.Units = value;
                            }
                            theSign = set.getVitalSign(VitalSign.DIASTOLIC_BP);
                            if (theSign != null)
                            {
                                theSign.Units = value;
                            }
                        }
                    }
                    else if (String.Equals(qualifier, "Qualifiers", StringComparison.CurrentCultureIgnoreCase))
                    {
                        theSign.Qualifiers = value;
                        if (keys[i] == "BP")
                        {
                            theSign = set.getVitalSign(VitalSign.SYSTOLIC_BP);
                            if (theSign != null)
                            {
                                theSign.Qualifiers = value;
                            }
                            theSign = set.getVitalSign(VitalSign.DIASTOLIC_BP);
                            if (theSign != null)
                            {
                                theSign.Qualifiers = value;
                            }
                        }
                    }
                    else
                    {
                        throw new Exception("Invalid qualifier: " + qualifier);
                    }
                }
            }
        }

        internal VitalSign getSignFromSet(VitalSignSet set, string key)
        {
            if (key == "TEMP")
            {
                return set.getVitalSign(VitalSign.TEMPERATURE);
            }
            if (key == "PULSE")
            {
                return set.getVitalSign(VitalSign.PULSE);
            }
            if (key == "RESP")
            {
                return set.getVitalSign(VitalSign.RESPIRATION);
            }
            if (key == "BP")
            {
                return set.getVitalSign(VitalSign.BLOOD_PRESSURE);
            }
            if (key == "WT")
            {
                return set.getVitalSign(VitalSign.WEIGHT);
            }
            if (key == "HT")
            {
                return set.getVitalSign(VitalSign.HEIGHT);
            }
            if (key == "PAIN")
            {
                return set.getVitalSign(VitalSign.PAIN);
            }
            if (key == "O2")
            {
                return set.getVitalSign(VitalSign.PULSE_OXYMETRY);
            }
            if (key == "CG")
            {
                return set.getVitalSign(VitalSign.CIRCUMFERENCE_GIRTH);
            }
            if (key == "CVP")
            {
                return set.getVitalSign(VitalSign.CENTRAL_VENOUS_PRESSURE);
            }
            if (key == "BMI")
            {
                return set.getVitalSign(VitalSign.BODY_MASS_INDEX);
            }
            return null;
        }
    }
}
