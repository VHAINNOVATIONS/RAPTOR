using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Collections;
using System.Text;
using gov.va.medora.utils;
using gov.va.medora.mdo.exceptions;
using gov.va.medora.mdo.src.mdo;
using System.Configuration;

namespace gov.va.medora.mdo.dao.vista
{
    public class VistaRadiologyDao : IRadiologyDao
    {
        AbstractConnection cxn = null;

        public VistaRadiologyDao(AbstractConnection cxn)
        {
            this.cxn = cxn;
        }

        #region Radiology Reports

        public RadiologyReport[] getRadiologyReports(string fromDate, string toDate, int nrpts)
        {
            return getRadiologyReports(cxn.Pid, fromDate, toDate, nrpts);
        }

        public RadiologyReport[] getRadiologyReports(string dfn, string fromDate, string toDate, int nrpts)
        {
            MdoQuery request = VistaUtils.buildReportTextRequest(dfn, fromDate, toDate, nrpts, "OR_R18:IMAGING~RIM;ORDV08;0;");
            string response = (string)cxn.query(request);
            return toRadiologyReports(response);
        }

        internal RadiologyReport[] toRadiologyReports(string response)
        {
            if (response == "")
            {
                return null;
            }
            string[] lines = StringUtils.split(response, StringUtils.CRLF);
            ArrayList lst = new ArrayList();
            RadiologyReport rec = null;
            for (int i = 0; i < lines.Length; i++)
            {
                if (lines[i] == "")
                {
                    continue;
                }
                string[] flds = StringUtils.split(lines[i], StringUtils.CARET);
                if (flds[1] == "[+]")
                {
                    lst.Add(rec);
                    continue;
                }
                int fldnum = Convert.ToInt32(flds[0]);
                switch (fldnum)
                {
                    // TODO
                    // need to consider the following case:
                    //1^NH CAMP PENDLETON;NH CAMP PENDLETON <--- not a recognized site code, should add to site 200
                    //2^05/24/2010 12:26
                    //3^L-SPINE, SERIES (3)
                    // lots more stuff in between
                    //10^[+]
                    case 1:
                        //if (rec != null)
                        //{
                        //    lst.Add(rec);
                        //}
                        rec = new RadiologyReport();
                        string[] parts = StringUtils.split(flds[1], StringUtils.SEMICOLON);
                        if (parts.Length == 2)
                        {
                            rec.Facility = new SiteId(parts[1], parts[0]);
                        }
                        else if (flds[1] != "")
                        {
                            rec.Facility = new SiteId(cxn.DataSource.SiteId.Id, flds[1]);
                        }
                        else
                        {
                            rec.Facility = cxn.DataSource.SiteId;
                        }
                        break;
                    case 2:
                        if (flds.Length == 2)
                        {
                            rec.Timestamp = VistaTimestamp.toUtcFromRdv(flds[1]);
                        }
                        break;
                    case 3:
                        if (flds.Length == 2)
                        {
                            rec.Title = flds[1];
                        }
                        break;
                    case 4:
                        if (flds.Length == 2)
                        {
                            rec.Status = flds[1];
                        }
                        break;
                    case 5:
                        if (flds.Length == 2)
                        {
                            rec.CaseNumber = flds[1];
                            rec.AccessionNumber = rec.getAccessionNumber(rec.Timestamp, rec.CaseNumber, DateFormat.ISO);
                        }
                        break;
                    case 6:
                        if (flds.Length == 2)
                        {
                            rec.Text += flds[1] + '\n';
                        }
                        break;
                        //case 7:
                        //    if (flds.Length == 2)
                        //    {
                        //        rec.Impression += flds[1] + '\n';
                        //    }
                        //    break;
                        //case 8:
                        //    if (flds.Length == 2)
                        //    {
                        //        rec.Text += flds[1] + '\n';
                        //    }
                        //    break;
                    case 9:
                        if (flds.Length == 2 && !String.IsNullOrEmpty(flds[1]))
                        {
                            string id = flds[1];
                            if (id.StartsWith("i"))
                            {
                                id = flds[1].Remove(0, 1); // for some reason, the IDs look like: i6888884.8972-1 but the 'i' character is not part of the ID
                            }
                            rec.Id = id;
                        }
                        break;
                }
            }
            //if (rec != null)
            //{
            //    lst.Add(rec);
            //}

            return (RadiologyReport[])lst.ToArray(typeof(RadiologyReport));
        }

        public RadiologyReport getImagingReport(string dfn, string accessionNumber)
        {
            return getReport(dfn, accessionNumber);
        }

        // TODO - need to return a RadiologyReport object here with correctly parsed data
        public RadiologyReport getReport(string dfn, string accessionNumber)
        {
            Dictionary<string, RadiologyReport> exams = getExamList(dfn);
            if (null == exams || !exams.ContainsKey(accessionNumber))
            {
                return null;
            }
            MdoQuery request = null;
            string response = "";
            try
            {
                string caseNumber = accessionNumber.Substring(accessionNumber.IndexOf('-') + 1);
                request = buildGetReportRequest(dfn, exams[accessionNumber].Id, caseNumber);
                response = (string)cxn.query(request);
                return toRadiologyReport(exams[accessionNumber], response);
            }
            catch (Exception exc)
            {
                throw new exceptions.MdoException(request, response, exc);
            }
        }

        internal MdoQuery buildGetReportRequest(string dfn, string id, string caseNumber)
        {
            VistaQuery vq = new VistaQuery("ORWRP REPORT TEXT");
            vq.addParameter(vq.LITERAL, dfn);
            vq.addParameter(vq.LITERAL, "18:IMAGING (LOCAL ONLY)~");
            vq.addParameter(vq.LITERAL, "");
            vq.addParameter(vq.LITERAL, "");
            vq.addParameter(vq.LITERAL, id + '#' + caseNumber);
            vq.addParameter(vq.LITERAL, "0");
            vq.addParameter(vq.LITERAL, "0");
            return vq;
        }

        public RadiologyReport toRadiologyReport(RadiologyReport report, string reportText)
        {
            report.Text = reportText;
            return report;
        }

        public RadiologyReport[] getRadiologyReportsBySite(string fromDate, string toDate, string siteCode)
        {
            return getRadiologyReports(fromDate, toDate, 5);
        }

        #endregion

        #region Get Exams
        /// <summary>
        /// Currently this dao method is not in use.  Unit test written for this DAO method.
        /// </summary>
        /// <param name="dfn"></param>
        /// <param name="timestamp"></param>
        /// <returns></returns>
        internal ArrayList getExams(string dfn, string timestamp)
        {
            DdrLister query = buildGetExamsQuery(dfn, timestamp);
            string[] response = query.execute();
            return toExamArrayList(response);
        }

        internal DdrLister buildGetExamsQuery(string dfn, string timestamp)
        {
            DdrLister result = new DdrLister(cxn);
            result.File = "70.03";
            result.Iens = "," + timestamp + "," + dfn + ",";
            result.Fields = ".01;2;3;17";
            result.Flags = "IP";
            result.Xref = "";
            return result;
        }

        internal ArrayList toExamArrayList(string[] response)
        {
            return null;
        }

        #endregion

        #region Get Exam List
        /// <summary>
        /// Get a Dictionary of RadiologyReport objects with the accession number as the key
        /// </summary>
        /// <param name="dfn">The patient's local ID</param>
        /// <returns>Dictionary of RadiologyReport objects with accession number as the key</returns>
        public Dictionary<string, RadiologyReport> getExamList(string dfn)
        {
            MdoQuery request = null;
            string response = "";
            try
            {
                request = buildGetExamListRequest(dfn);
                response = (string)cxn.query(request);
                return toImagingExamIds(response);
            }
            catch (Exception exc)
            {
                throw new exceptions.MdoException(request, response, exc);
            }
        }

        internal MdoQuery buildGetExamListRequest(string dfn)
        {
            VistaUtils.CheckRpcParams(dfn);
            VistaQuery vq = new VistaQuery("ORWRA IMAGING EXAMS1");
            vq.addParameter(vq.LITERAL, dfn);
            return vq;
        }

        internal Dictionary<string, RadiologyReport> toImagingExamIds(string response)
        {
            if (String.IsNullOrEmpty(response))
            {
                return null;
            }
            Dictionary<string, RadiologyReport> result = new Dictionary<string, RadiologyReport>();
            string[] rex = StringUtils.split(response, StringUtils.CRLF);
            rex = StringUtils.trimArray(rex);
            for (int i = 0; i < rex.Length; i++)
            {
                RadiologyReport newReport = new RadiologyReport();
                string[] flds = StringUtils.split(rex[i], StringUtils.CARET);
                newReport.AccessionNumber = newReport.getAccessionNumber(flds[2], flds[4], DateFormat.VISTA);
                newReport.Id = flds[1];
                newReport.Timestamp = flds[2];
                newReport.Title = flds[3];
                newReport.CaseNumber = flds[4];
                newReport.Status = flds[5];
                newReport.Exam = new ImagingExam();
                newReport.Exam.Status = (flds[8].Split(new char[] { '~' }))[1]; // e.g. 9~COMPLETE
                // maybe map some more fields? lot's of stuff being returned by RPC. For example:
                //ANN ARBOR VAMC;506
                //^6899085.8548-1
                //^3100914.1451
                //^TIBIA & FIBULA 2 VIEWS
                //^794
                //^Verified
                //^
                //^878633
                //^9~COMPLETE
                //^AA RADIOLOGY,AA
                //^RAD~GENERAL RADIOLOGY
                //^
                //^73590
                //^20323714
                //^N
                //^
                //^
                result.Add(newReport.AccessionNumber, newReport); 
            }
            return result;
        }


        #endregion

        #region Raptor Enhancements

        #region Exam and Order Cancellation
        /// <summary>
        /// Cancel an exam. If all exams that reference the same order/request are canceled, this
        /// function can also cancel/hold the order (if the appropriate parameters are provided)
        /// </summary>
        /// <param name="caseNumber">
        ///   String of exam/case identifiers separated by '^':
        ///	   ^01: IEN of the patient in the RAD/NUC MED PATIENT file (#70)
        ///    ^02: IEN in the REGISTERED EXAMS multiple (sub-file #70.02)
        ///    ^03: IEN in the EXAMINATIONS multiple (sub-file #70.03)
        /// </param>
        /// <param name="reasonIen">From RAD/NUC MED REASON (#75.2) file</param>
        /// <param name="miscOptions"></param>
        /// <param name="flags">
        ///   Flags that control execution (can be combined):
        ///    A  Cancel all related exams/cases (those that
        ///       reference the same order).
 
        ///    O  Cancel/hold the related order after successful
        ///       exam cancelation.      The order will be canceled or put on hold only
        ///       if there are no more active cases associated
        ///       with it.
 
        ///       Otherwise, the error code -42 will be returned.
        ///       Use the "A" flag to cancel all related exams
        ///       and guarantee the order cancelation.
        /// </param>
        public void cancelExam(String examIdentifier, String reasonIen, bool cancelAssociatedOrder = true, String holdDescription = null)
        {
            MdoQuery request = buildCancelExamRequest(examIdentifier, reasonIen, cancelAssociatedOrder, holdDescription);
            String response = (String)this.cxn.query(request, new MenuOption("RAPT RAPTOR"));
            toCancelExamResponse(response);
        }

        internal MdoQuery buildCancelExamRequest(String examIdentifier, String reasonIen, bool cancelAssociatedOrder, String holdDescription)
        {
            VistaQuery vq = new VistaQuery("RAMAG EXAM CANCEL");
            vq.addParameter(vq.LITERAL, examIdentifier);
            vq.addParameter(vq.LITERAL, reasonIen);

            if (!String.IsNullOrEmpty(holdDescription))
            {
                String[] holdDescrLines = StringUtils.split(holdDescription, StringUtils.CRLF);
                DictionaryHashList miscOptionDHL = new DictionaryHashList();
                String currentLineNo = "1";
                foreach (String line in holdDescrLines)
                {
                    miscOptionDHL.Add(currentLineNo, String.Format("HOLDESC^{0}^{1}", currentLineNo, line));
                    currentLineNo = Convert.ToString(Convert.ToInt32(currentLineNo) + 1);
                }
                vq.addParameter(vq.LIST, miscOptionDHL);
            }
            else
            {
                vq.addParameter(vq.LIST, new DictionaryHashList());
            }

            if (cancelAssociatedOrder) { vq.addParameter(vq.LITERAL, "O"); } else { vq.addParameter(vq.LITERAL, "A"); }
             
            return vq;
        }

        internal void toCancelExamResponse(String queryResponse)
        {
            if (!String.IsNullOrEmpty(queryResponse) && queryResponse.StartsWith("0"))
            {
                return;
            }
            else
            {
                String[] lines = StringUtils.split(queryResponse, StringUtils.CRLF);
                String errorCode = lines[0];
                String message = "";
                if (lines.Length > 1)
                {
                    message = String.Join(StringUtils.CRLF, lines, 1, lines.Length - 1);
                }
                throw new MdoException(String.Format("Problem cancelling exam: {0} - {1}", errorCode, message));
            }
        }


        /// <summary>
        /// Cancel Radiology Order. RPC does not cancel order if there are active cases in file #70 
        /// </summary>
        /// <param name="orderId">Order IEN from file 75.1</param>
        /// <param name="reasonIen">Reason IEN from file </param>
        /// <param name="holdDescription">Order will be placed on hold (NOT cancelled) if a hold description is supplied</param>
        public void cancelRadiologyOrder(String orderId, String reasonIen, String holdDescription = null)
        {
            MdoQuery request = buildCancelRadiologyOrderRequest(orderId, reasonIen, holdDescription);
            String response = (String)this.cxn.query(request);
            toCancelRadiologyOrderResponse(response);
        }

        internal MdoQuery buildCancelRadiologyOrderRequest(String orderId, String reasonIen, String holdDescription)
        {
            VistaQuery vq = new VistaQuery("RAMAG ORDER CANCEL");
            vq.addParameter(vq.LITERAL, orderId);
            vq.addParameter(vq.LITERAL, reasonIen);
            
            if (!String.IsNullOrEmpty(holdDescription))
            {
                String[] holdDescrLines = StringUtils.split(holdDescription, StringUtils.CRLF);
                DictionaryHashList miscOptionDHL = new DictionaryHashList();
                String currentLineNo = "1";
                foreach (String line in holdDescrLines)
                {
                    miscOptionDHL.Add(currentLineNo , String.Format("HOLDESC^{0}^{1}", currentLineNo, line));
                    currentLineNo = Convert.ToString(Convert.ToInt32(currentLineNo) + 1);
                }
                vq.addParameter(vq.LIST, miscOptionDHL);
            }
            else
            {
                vq.addParameter(vq.LIST, new DictionaryHashList());
            }

            return vq;
        }

        internal void toCancelRadiologyOrderResponse(String queryResponse)
        {
            if (!String.IsNullOrEmpty(queryResponse) && queryResponse.StartsWith("0"))
            {
                return;
            }
            else
            {
                String[] lines = StringUtils.split(queryResponse, StringUtils.CRLF);
                String errorCode = lines[0];
                String message = "";
                if (lines.Length > 1)
                {
                    message = String.Join(StringUtils.CRLF, lines, 1, lines.Length - 1);
                }
                throw new MdoException(String.Format("Problem cancelling exam: {0} - {1}", errorCode, lines));
            }
        }

        #endregion

        #region RAPTOR Enhancements - Supporting Calls

        /// <summary>
        ///  I    Ionic Iodinated
        ///  N    Non-ionic Iodinated
        ///  L    Gadolinium
        ///  C    Cholecystographic
        ///  G    Gastrografin
        ///  B    Barium
        ///  M    unspecified contrast media
        /// </summary>
        /// <returns></returns>
        public Dictionary<String, String> getContrastMedia()
        {
            return new Dictionary<string, string>()
            {
               { "I", "Ionic Iodinated" },
               { "N", "Non-ionic Iodinated" },
               { "L", "Gadolinium" },
               { "C", "Cholecystographic" }, 
               { "G", "Gastrografin" }, 
               { "B", "Barium" }, 
               { "M", "unspecified contrast media" } 
            };
        }

        /// <summary>
        /// Fetch the complications listed in Vista file 78.1 - COMPLICATION TYPES
        /// </summary>
        /// <returns></returns>
        public Dictionary<String, String> getComplications()
        {
            DdrLister request = buildGetComplicationsRequest();
            String[] response = request.execute();
            return toComplications(response);
        }

        internal DdrLister buildGetComplicationsRequest()
        {
            return new DdrLister(this.cxn)
            {
                File = "78.1",
                Fields = ".01",
                Flags = "IP",
                Xref = "#"
            };
        }

        internal Dictionary<String, String> toComplications(String[] response)
        {
            Dictionary<String, String> result = new Dictionary<string, string>();

            foreach (String rec in response)
            {
                String[] pieces = StringUtils.split(rec, StringUtils.CARET);
                result.Add(pieces[0], pieces[1]);
            }

            return result;
        }

        /// <summary>
        /// Refactored to use CPRS order RPCs - currently wrapping VistaRadiologyDao.getOrderCancellationReasons
        /// </summary>
        /// <returns></returns>
        public IList<RadiologyCancellationReason> getCancellationReasons()
        {
            IList<RadiologyCancellationReason> result = new List<RadiologyCancellationReason>();
            Dictionary<String, String> cancelReasons = this.getOrderCancellationReasons();
            foreach (String key in cancelReasons.Keys)
            {
                result.Add(new RadiologyCancellationReason() { Id = key, Reason = cancelReasons[key] });
            }
            return result;

            //DdrLister request = buildGetCancellationReasonsRequest();
            //String[] response = request.execute();
            //return toCancellationReasons(response);
        }

        internal DdrLister buildGetCancellationReasonsRequest()
        {
            return new DdrLister(this.cxn)
            {
                Fields = ".01;2;3;4",
                File = "75.2",
                Flags = "IP",
                Xref = "#"
            };
        }

        internal IList<RadiologyCancellationReason> toCancellationReasons(String[] response)
        {
            IList<RadiologyCancellationReason> result = new List<RadiologyCancellationReason>();

            if (response == null || response.Length == 0) { return result; }

            foreach (String line in response)
            {
                // in results section of DDR results
                String[] pieces = StringUtils.split(line, StringUtils.CARET);
                result.Add(new RadiologyCancellationReason()
                {
                    Id = pieces[0],
                    Reason = pieces[1],
                    Type = pieces[2],
                    Synonym = pieces[3],
                    NatureOfOrderActivity = pieces[4]
                });
            }

            return result;
        }

        public IList<ImagingExam> getExamsByPatient(string dfn)
        {
            DdrLister query = buildGetExamsListQuery(dfn);
            string[] response = query.execute();
            IList<ImagingExam> examList = toExamListFromDDR(response);

            supplementExamDetails(dfn, examList);
            return examList;
        }

        /// <summary>
        /// .01 - Exam Date
        /// 2 - Type of Imaging (pointer to file 79.2 - IMAGING TYPE)
        /// 3 - Hospital Division (pointer to file 79 - RAD/NUC MED DIVISION)
        /// 4 - Imaging Location (pointer to file 79.1 - IMAGING LOCATION)
        /// 5 - Exam Set (1/0 - yes/no)
        /// </summary>
        /// <param name="dfn"></param>
        /// <returns></returns>
        internal DdrLister buildGetExamsListQuery(string dfn)
        {
            DdrLister result = new DdrLister(cxn);
            result.File = "70.02";
            result.Iens = "," + dfn + ",";
            result.Fields = ".01;2;3;4;5";
            result.Flags = "IP";
            result.Xref = "#";
            return result;
        }

        internal IList<ImagingExam> toExamListFromDDR(string[] response)
        {
            IList<ImagingExam> result = new List<ImagingExam>();
            
            if (response != null && response.Length > 0)
            {
                foreach (String examStr in response)
                {
                    String[] pieces = StringUtils.split(examStr, StringUtils.CARET);
                    result.Add(new ImagingExam()
                    {
                        Id = pieces[0],
                        Timestamp = pieces[1],
                        ImagingType = pieces[2],
                        // TBD - need to map Hospital Division to some new property??
                        ImagingLocation = new HospitalLocation(pieces[4], "" /* TODO - get name? */)
                        // TBD - need to map Exam Set field to some new property??
                    });
                }
            }

            return result;
        }

        /// <summary>
        /// Loop through exams and make call to DDR GETS ENTRY DATA to 
        /// supplement the list of imaging exams with data from subfile 70/70.02/70.03 - EXAMINATIONS
        /// </summary>
        /// <param name="exams"></param>
        internal void supplementExamDetails(String patientId, IList<ImagingExam> exams)
        {
            foreach (ImagingExam exam in exams)
            {
                DdrLister examDetailsRequest = buildGetExamDetailsRequest(patientId, exam);
                String[] response = examDetailsRequest.execute();
                
                //Dictionary<String, String> details = new DdrGetsEntry(null).convertToFieldValueDictionary(examDetailsRequest.execute());
                supplementExamDetailsWithDdrResult(patientId, exam, response);
            }
        }

        internal void supplementExamDetailsWithDdrResult(String patientId, ImagingExam exam, String[] imagingExams)
        {
            // TBD - should each entry in subfile 70.03 be an ImagingExam object? Should the ImagingExam from 70.02 be cloned 
            // and then the properties from each record in 70.03 copied over? Quick spelunking shows mostly 1:1 relationship 70.02 : 70.03

            // a DdrGetsEntry call was required because CLINICAL HISTORY (field #400) is a WP field. So, we're going to fetch
            // the whole record instead so we can use the external values where available
            
            foreach (String exam70x03 in imagingExams)
            {
                String[] pieces = StringUtils.split(exam70x03, StringUtils.CARET);
                // DDR LISTER fields: IEN^.01^2^3^3.5^4^6^7^8^10^11^12^13^14^16^16.5^17^21^27^31
                // Index:              0   1  2 3  4  5 6 7 8  9 10 11 12 13 14  15  16 17 18 19 

                String[] ddrGetsEntryResult = buildGetExamDdrGetsEntryQuery(patientId, exam.Id, pieces[0]).execute();
                DdrGetsEntryDictionary rec = DdrGetsEntryDictionary.parse(ddrGetsEntryResult);

                exam.CaseNumber = rec.safeGetValue(".01"); // pieces[1];
                exam.Status = rec.safeGetValue("3"); // pieces[3];
                exam.AccessionNumber = rec.safeGetValue("31"); // pieces[19];
                exam.ClinicalHistory = rec.safeGetValue("400");
                // TODO - change object model as needed to accomodate other properties
            }
            //if (details.ContainsKey(".01")) { exam.CaseNumber = details[".01"]; }
            //if (details.ContainsKey("2")) { /* TBD - map PROCEDURE (pointer to RAD/NUC MED PROCEDURES - #71) field? */ }
            //if (details.ContainsKey("3")) { exam.Status = details["3"]; } // pointer to EXAMINATION STATUS file #72 - DDR GETS ENTRY request should've brought external value though!
            //if (details.ContainsKey("3.5")) { /* TBD - map CANCELLATION REASON (pointer to RAD/NUC MED REASON - 75.2) field? */ }
            //if (details.ContainsKey("4")) { /* TBD - map CATEGORY OF EXAM field? */ }
            //if (details.ContainsKey("10")) { /* CONTRAST MEDIA USED */ }
            //if (details.ContainsKey("11")) { if (exam.Order == null) { exam.Order = new Order() { Id = details["10"] }; } } // pointer to RAD/NUC MED ORDERS #75.1
            //if (details.ContainsKey("12")) { /* PRIMARY INTERPRETING RESIDENT */ }
            //if (details.ContainsKey("13")) { /* PRIMARY DIAGNOSTIC CODE */ }
            //if (details.ContainsKey("14")) { /* REQUESTING PHYSICIAN */ }
            //if (details.ContainsKey("16")) { /* COMPLICATION - pointer to file #78.1 */ }
            //if (details.ContainsKey("16")) { /* COMPLICATION TEXT */ }
            //if (details.ContainsKey("17")) { /* REPORT TEXT - pointer to file #74 */ }
            //if (details.ContainsKey("21")) { /* REQUESTED DATE */ }
            //if (details.ContainsKey("27")) { /* VISIT - pointer to file #9000010 */ }
            //if (details.ContainsKey("31")) { exam.AccessionNumber = details["31"]; }
            //if (details.ContainsKey("400")) { exam.ClinicalHistory = details["400"]; }
        }

        internal DdrLister buildGetExamDetailsRequest(String patientId, ImagingExam exam)
        {
            return new DdrLister(this.cxn)
            {
                File = "70.03",
                //Fields = ".01;2;3;3.5;4;6;7;8;10;11;12;13;14;16;16.5;17;21;27;31",
                Fields = ".01",
                Flags = "IP",
                Iens = "," + exam.Id + "," + patientId + ",",
                Xref = "#"
            };
        }

        internal DdrGetsEntry buildGetExamDdrGetsEntryQuery(String patientId, String examIen70x02, String examIen70x03)
        {
            return new DdrGetsEntry(this.cxn)
            {
                Fields = "*",
                File = "70.03",
                Flags = "IE",
                Iens = examIen70x03 + "," + examIen70x02 + "," + patientId + ","
            };
        }

        /// <summary>
        /// Get an order from the RAD/NUC MED ORDERS file #75.1
        /// </summary>
        /// <param name="orderIen"></param>
        /// <returns></returns>
        public  Order getRadNucOrder(String orderIen)
        {
            DdrGetsEntry request = buildGetRadNucMedOrderRequest(orderIen);
            String[] results = request.execute();
            Dictionary<String, String> resultsDict = DdrGetsEntry.convertResultToDictionary(results);
            return toRadNucOrder(resultsDict);
        }

        internal DdrGetsEntry buildGetRadNucMedOrderRequest(String orderIen)
        {
            return new DdrGetsEntry(this.cxn)
            {
                File = "75.1",
                Fields = "*",
                Flags = "IE",
                Iens = String.Concat(orderIen, ",")
            };
        }

        internal Order toRadNucOrder(Dictionary<String, String> resultsDict)
        {
            return new Order()
            {
                Id = resultsDict["IEN"],
                PatientId = resultsDict[".01"],
                Text = resultsDict["1.1"],
                Status = resultsDict["5"],
                Urgency = resultsDict["6"],
                Provider = new User() { Id = resultsDict["14"] },
                WhoEnteredId = resultsDict["15"],
                Detail = resultsDict["400"]
            };
        }

        #endregion

        /// <summary>
        /// Register an exam
        /// </summary>
        /// <param name="orderId">Order IEN from file 75.1</param>
        /// <param name="examDateTime">YYYYMMDDHHMM</param>
        /// <param name="examCategory">
        /// 'I' FOR INPATIENT
        /// 'O' FOR OUTPATIENT
        /// 'C' FOR CONTRACT
        /// 'S' FOR SHARING
        /// 'E' FOR EMPLOYEE
        /// 'R' FOR RESEARCH
        /// </param>
        /// <param name="hospitalLocation">IEN of outpatient location from file 44 (value of file 70.03 -> field 8)</param>
        /// <param name="ward">IEN of inpatient ward from file 42 (value of file 70.03 -> field 6)</param>
        /// <param name="service">IEN of inpatient service from file 49 (value of file 70.03 -> field 7)</param>
        /// <returns></returns>
        public ImagingExam registerExam(
            String orderId, 
            String examDateTime, 
            String examCategory, 
            String hospitalLocation, 
            String ward, 
            String service,
            String technologistComment)
        {
            if ((String.Equals(examCategory, "O", StringComparison.CurrentCultureIgnoreCase)
                || String.Equals(examCategory, "E", StringComparison.CurrentCultureIgnoreCase))
                && String.IsNullOrEmpty(hospitalLocation))
            {
                throw new MdoException("Must supply a hospital location for OUTPATIENT and EMPLOYEE exams");
            }

            MdoQuery request = buildRegisterExamRequest(orderId, examDateTime, examCategory, hospitalLocation, ward, service, technologistComment);
            String response = (String)this.cxn.query(request);
            return toImagingExam(response);
        }

        internal MdoQuery buildRegisterExamRequest(String orderId, String examDateTime, String examCategory, String hospitalLocation,
            String wardLocation, String service, String techComment)
        {
            VistaQuery vq = new VistaQuery("RAMAG EXAM REGISTER");
            vq.addParameter(vq.LITERAL, orderId);
            vq.addParameter(vq.LITERAL, examDateTime);
            DictionaryHashList miscParams = new DictionaryHashList();
            Int32 miscParamCount = 1;
            
            miscParams.Add(miscParamCount.ToString(), "FLAGS^^A");
            miscParamCount++;

            miscParams.Add(miscParamCount.ToString(), "EXAMCAT^^" + examCategory);
            miscParamCount++;


            if (!String.IsNullOrEmpty(hospitalLocation))
            {
                miscParams.Add(miscParamCount.ToString(), "PRINCLIN^^" + hospitalLocation);
                miscParamCount++;
            }
            if (!String.IsNullOrEmpty(wardLocation))
            {
                miscParams.Add(miscParamCount.ToString(), "WARD^^" + wardLocation);
                miscParamCount++;
            }
            if (!String.IsNullOrEmpty(service))
            {
                miscParams.Add(miscParamCount.ToString(), "SERVICE^^" + service);
                miscParamCount++;
            }
            if (!String.IsNullOrEmpty(techComment))
            {
                miscParams.Add(miscParamCount.ToString(), "TECHCOMM^^" + techComment);
                miscParamCount++;
            }

            vq.addParameter(vq.LIST, miscParams);

            return vq;
        }

        // sample response: "1\r\n100856^6859079.8899^1^72^092014-72^201409201100-0500\r\n"
        internal ImagingExam toImagingExam(String response)
        {
            if (String.IsNullOrEmpty(response))
            {
                throw new ArgumentNullException("Invalid response for imaging exam");
            }


            throwExceptionIfError(response);


            String[] pieces = StringUtils.split(response, StringUtils.CRLF);
            String[] newExam = StringUtils.split(pieces[1], StringUtils.CARET);

            return new ImagingExam()
            {
                Id = String.Concat(",", newExam[2], ",", newExam[1], ",", newExam[0], ","),
                CaseNumber = newExam[3],
                Timestamp = newExam[5]
            };
        }

        /// <summary>
        /// The RAMAG RPCs use the same error message format so we can standardize parsing
        /// </summary>
        /// <param name="response"></param>
        internal void throwExceptionIfError(String response)
        {
            String[] pieces = StringUtils.split(response, StringUtils.CRLF);
            if (response.StartsWith("-")) // error!
            {
                String errorCode = "";
                String message = "";
                if (pieces.Length > 1)
                {
                    String[] msgLine = StringUtils.split(pieces[1], StringUtils.CARET);
                    errorCode = msgLine[0];
                    if (msgLine.Length > 1)
                    {
                        message = msgLine[1];
                    }
                }
                throw new MdoException(message);
            }
        }
        #endregion

        #region Raptor Order APIs Enhancments

        /// <summary>
        /// Currently just wrapping VistaOrdersDao.getDiscontinueReasons and converting results to Dictionary<String, String>
        /// </summary>
        /// <returns></returns>
        public Dictionary<String, String> getOrderCancellationReasons()
        {
            StringDictionary sd = new VistaOrdersDao(this.cxn).getDiscontinueReasons();
            Dictionary<String, String> sdAsDict = new Dictionary<string,string>();
            foreach (String key in sd.Keys)
            {
                sdAsDict.Add(key, sd[key]);
            }
            return sdAsDict;
        }

        internal Order cancelOrder(String orderId, String providerDuz, String clinicIen, String reasonIen)
        {
            MdoQuery request = buildCancelOrderRequest(orderId, providerDuz, clinicIen, reasonIen);
            String response = (String)this.cxn.query(request);
            return toCancelOrder(response);
        }

        internal MdoQuery buildCancelOrderRequest(String orderId, String providerDuz, String clinicIen, String reasonIen)
        {
            VistaQuery vq = new VistaQuery("ORWDXA DC");

            vq.addParameter(vq.LITERAL, orderId); // should be formatted like 12345;1
            vq.addParameter(vq.LITERAL, providerDuz);
            vq.addParameter(vq.LITERAL, clinicIen);
            vq.addParameter(vq.LITERAL, reasonIen);
            vq.addParameter(vq.LITERAL, "0");
            vq.addParameter(vq.LITERAL, "0");

            return vq;
        }

        internal Order toCancelOrder(String response)
        {
            //Results -----------------------------------------------------------------
            //~34902;2^36^3141107.0622^^^11^2^^^983^PROVIDER,ONE^^0^1^^^^^PRIMARY CARE:23^^1^0
            //tDiscontinue MAGNETIC RESONANCE SCAN *UNSIGNED*
            //t<Requesting Physician Cancelled>

            if (String.IsNullOrEmpty(response) || !response.StartsWith("~"))
            {
                throw new MdoException("Unable to cancel order: " + response);
            }

            Order result = new Order();
            String[] lines = StringUtils.split(response, StringUtils.CRLF);
            String[] piecesLine1 = StringUtils.split(lines[0], StringUtils.CARET);

            String orderEventId = piecesLine1[0].Substring(1); // get rid ot tilde
            String eventDt = piecesLine1[2];
            String responseText = lines[1].Substring(1); // lines start with 't'
            String reasonFromVista = lines[2].Substring(1);

            result.Id = orderEventId;
            result.Text = responseText;

            return result;
        }

        public Order discontinueOrder(string orderIen, string providerDuz, String locationIen, string reasonIen)
        {
            return discontinueOrder(cxn.Pid, orderIen, providerDuz, locationIen, reasonIen);
        }
        
        public Order discontinueOrder(String patientId, string orderIen, string providerDuz, String locationIen, string reasonIen)
        {
            if (String.IsNullOrEmpty(orderIen))
            {
                throw new ArgumentException("No order ID");
            }
            if (String.IsNullOrEmpty(providerDuz))
            {
                throw new ArgumentException("No user ID");
            }
            if (String.IsNullOrEmpty(locationIen))
            {
                throw new ArgumentException("No location ID");
            }
            if (String.IsNullOrEmpty(reasonIen))
            {
                throw new ArgumentException("No reason ID");
            }

            String userId = cxn.Uid;

            VistaUserDao userDao = new VistaUserDao(cxn);
            bool providerHasProvider = userDao.hasPermission(providerDuz, new SecurityKey("", "PROVIDER"));
            if (!providerHasProvider)
            {
                throw new ArgumentException("The account with the DUZ specified does not hold the PROVIDER key");
            }

            VistaOrdersDao orderDao = new VistaOrdersDao(cxn);
            Order order = orderDao.getOrder(orderIen);
            if (order == null)
            {
                throw new MdoException("No such order");
            }

            string msg = orderDao.validateOrderActionNature(order.Id, "DC", providerDuz, ""); // TBD - orderIen -> order.Id??
            if (msg != "OK")
            {
                throw new MdoException(msg);
            }
            msg = orderDao.getComplexOrderMsg(order.Id); // TBD - orderIen -> order.Id??
            if (msg != "")
            {
                throw new MdoException(msg);
            }

            if (!orderDao.lockOrdersForPatient(patientId))
            {
                throw new MdoException("Unable to lock orders for patient");
            }

            msg = orderDao.lockOrder(order.Id);
            if (msg != "OK")
            {
                orderDao.unlockOrdersForPatient();
                throw new MdoException(msg);
            }

            Order canceledOrder = cancelOrder(order.Id, providerDuz, locationIen, reasonIen);

            orderDao.unlockOrder(canceledOrder.Id);
            orderDao.unlockOrdersForPatient(patientId);

            return canceledOrder;
        }


        public Order discontinueAndSignOrder(string orderIen, string providerDuz, String locationIen, string reasonIen, String eSig)
        {
            return discontinueAndSignOrder(cxn.Pid, orderIen, providerDuz, locationIen, reasonIen, eSig);
        }

        public Order discontinueAndSignOrder(String patientId, string orderIen, string providerDuz, String locationIen, string reasonIen, String eSig)
        {
            if (String.IsNullOrEmpty(orderIen))
            {
                throw new ArgumentException("No order ID");
            }
            if (String.IsNullOrEmpty(providerDuz))
            {
                throw new ArgumentException("No user ID");
            }
            if (String.IsNullOrEmpty(locationIen))
            {
                throw new ArgumentException("No location ID");
            }
            if (String.IsNullOrEmpty(reasonIen))
            {
                throw new ArgumentException("No reason ID");
            }
            if (String.IsNullOrEmpty(eSig))
            {
                throw new ArgumentException("No electronic signature code");
            }

            String userId = cxn.Uid;

            VistaUserDao userDao = new VistaUserDao(cxn);
            bool providerHasProvider = userDao.hasPermission(providerDuz, new SecurityKey("", "PROVIDER"));
            if (!providerHasProvider)
            {
                throw new ArgumentException("The account with the DUZ specified does not hold the PROVIDER key");
            }

            if (!userDao.isValidEsig(eSig))
            {
                throw new MdoException("Invalid signature code");
            }

            VistaOrdersDao orderDao = new VistaOrdersDao(cxn);
            Order order = orderDao.getOrder(orderIen);
            if (order == null)
            {
                throw new MdoException("No such order");
            }

            if (String.Equals(order.Status, "DISCONTINUED", StringComparison.CurrentCultureIgnoreCase))
            {
                throw new ArgumentException("Order is already discontinued");
            }

            bool userHasProvider = String.Equals(userId, providerDuz) ? providerHasProvider : userDao.hasPermission(userId, new SecurityKey("", "PROVIDER"));
            bool userHasOremas = userDao.hasPermission(userId, new SecurityKey("", "OREMAS")); // need this for some decisions so fetch even if user holds superceding PROVIDER key
            bool usingWrittenOnChart = false;
            bool okToDcAndSign = false; //using this to simplify logic and skip checks
            // allow this to be configurable
            bool okToCancelOrderFromOtherProvider = false;
            Boolean.TryParse(ConfigurationManager.AppSettings["AllowOrderDcFromOtherProvider"], out okToCancelOrderFromOtherProvider);

            String originalOrderProvider = order.Provider.Uid; //.Provider.Id;

            if (String.Equals(originalOrderProvider, userId))
            {
                okToDcAndSign = true;
            }

            if (!okToDcAndSign)
            {
                if (!String.Equals(originalOrderProvider, userId) && userHasProvider)
                {
                    if (okToCancelOrderFromOtherProvider)
                    {
                        okToDcAndSign = true;
                    }
                    else
                    {
                        throw new ArgumentException("Providers may not sign discontinue order request for another provider's order. Use discontinue order without signature");
                    }
                }
            }

            if (!okToDcAndSign)
            {
                if (!userHasProvider && !userHasOremas)
                {
                    throw new UnauthorizedAccessException("User does not have appropriate keys for cancel and sign");
                }
            }

            if (!okToDcAndSign)
            {
                if (!userHasProvider && userHasOremas)
                {
                    okToDcAndSign = usingWrittenOnChart = true;
                }
            }

            string msg = orderDao.validateOrderActionNature(order.Id, "DC", providerDuz, ""); // TBD - orderIen -> order.Id??
            if (msg != "OK")
            {
                throw new MdoException(msg);
            }
            msg = orderDao.getComplexOrderMsg(order.Id); // TBD - orderIen -> order.Id??
            if (msg != "")
            {
                throw new MdoException(msg);
            }

            if (!orderDao.lockOrdersForPatient(patientId))
            {
                throw new MdoException("Unable to lock orders for patient");
            }

            msg = orderDao.lockOrder(order.Id);
            if (msg != "OK")
            {
                orderDao.unlockOrdersForPatient();
                throw new MdoException(msg);
            }

            Order canceledOrder = cancelOrder(order.Id, providerDuz, locationIen, reasonIen);

            orderDao.signOrder(patientId, canceledOrder.Id, providerDuz, locationIen, eSig, true, usingWrittenOnChart);

            orderDao.unlockOrder(canceledOrder.Id);
            orderDao.unlockOrdersForPatient(patientId);

            return canceledOrder;
        }

        public void signOrder(String orderId, String providerDuz, String locationIen, String eSig)
        {
            if (String.IsNullOrEmpty(orderId) || String.IsNullOrEmpty(providerDuz) || String.IsNullOrEmpty(locationIen) || String.IsNullOrEmpty(eSig))
            {
                throw new ArgumentException("Must supply all arguments!");
            }

            VistaOrdersDao orderDao = new VistaOrdersDao(this.cxn);
            Order order = orderDao.getOrder(orderId);

            if (order == null)
            {
                throw new ArgumentException("Invalid order IEN");
            }

            VistaUserDao userDao = new VistaUserDao(cxn);
            bool providerHasProvider = userDao.hasPermission(providerDuz, new SecurityKey("", "PROVIDER"));
            if (!providerHasProvider)
            {
                throw new MdoException("Provider DUZ specified does not have PROVIDER key");
            }

            if (!userDao.isValidEsig(eSig))
            {
                throw new MdoException("Invalid signature code");
            }

            // NOTE:: these are the SAME business rules as found in discontinueAndSign above - must change them BOTH PLACES if they need updated!!!
            String userId = cxn.Uid;

            bool userHasProvider = String.Equals(userId, providerDuz) ? providerHasProvider : userDao.hasPermission(userId, new SecurityKey("", "PROVIDER"));
            bool userHasOremas = userDao.hasPermission(userId, new SecurityKey("", "OREMAS")); // need this for some decisions so fetch even if user holds superceding PROVIDER key
            bool usingWrittenOnChart = false;
            bool okToDcAndSign = false; //using this to simplify logic and skip checks
            // allow this to be configurable
            bool okToCancelOrderFromOtherProvider = false;
            Boolean.TryParse(ConfigurationManager.AppSettings["AllowOrderDcFromOtherProvider"], out okToCancelOrderFromOtherProvider);

            String originalOrderProvider = order.Provider.Uid;

            if (String.Equals(originalOrderProvider, userId))
            {
                okToDcAndSign = true;
            }

            if (!okToDcAndSign)
            {
                if (!String.Equals(originalOrderProvider, userId) && userHasProvider)
                {
                    if (okToCancelOrderFromOtherProvider)
                    {
                        okToDcAndSign = true;
                    }
                    else
                    {
                        throw new ArgumentException("Providers may not sign another provider's order.");
                    }
                }
            }

            if (!okToDcAndSign)
            {
                if (!userHasProvider)
                {
                    if (!userHasOremas)
                    {
                        throw new UnauthorizedAccessException("User does not have appropriate keys for sign");
                    }
                }
            }

            if (!okToDcAndSign)
            {
                if (!userHasProvider && userHasOremas)
                {
                    okToDcAndSign = usingWrittenOnChart = true;
                }
            }

            orderDao.lockOrder(orderId);
            orderDao.signOrder(orderId, providerDuz, locationIen, eSig, true, usingWrittenOnChart);
            orderDao.unlockOrder(orderId);
        }

        #endregion
    }
}
