using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using gov.va.medora.mdo;
using System.IO;
using gov.va.medora.mdo.dao.vista;
using gov.va.medora.utils.mock;
using System.Diagnostics;
using gov.va.medora.mdo.dao.mock;
using gov.va.medora.mdo.dao.sqlite;

namespace gov.va.medora.mdo.dao.vista
{
    public class XVistaConnection : VistaConnection
    {
        MockXmlSource xmlSource;
        Boolean saveResults;
        Boolean saveAuthConnect;
        Boolean updateResults;
        Boolean displayRPCList;

        List<String> requests;
        List<String> responses;

        public List<String> Requests
        {
            get { return this.requests; }
        }

        public List<String> Responses
        {
            get { return this.responses; }
        }

        public Boolean SaveResults
        {
            get { return this.saveResults; }
            set { this.saveResults = value; }
        }

        public Boolean SaveAuthConnect
        {
            get { return this.saveAuthConnect; }
            set { this.saveAuthConnect = value; }
        }

        public Boolean UpdateResults
        {
            get { return this.updateResults; }
            set { this.updateResults = value; }
        }


        public Boolean DisplayRPCList
        {
            get { return this.displayRPCList; }
            set { this.displayRPCList = value; }
        }

        public int RequestsToWrite
        {
            get { return requests.Count; }
        }

        public void resetRequests()
        {
            requests.Clear();
        }

        SqliteDao _sqliteDao;
        string _mockSiteId;

        //public String OverrideMockFile
        //{
        //    set 
        //    {
        //        string fileId = value + getMockSiteId(this.DataSource.SiteId.Id);
        //        setXmlSource(fileId);
        //    }
        //}

        //public void setXmlSource(string siteId)
        //{
        //    if (null != xmlSource)
        //    {
        //        xmlSource = null;
        //    }

        //   xmlSource = new MockXmlSource(siteId);
        //}
        public string getMockSiteId(string id)
        {
            if (id == "204")
            {
                return "204";
            }
            if (id == "506")
            {
                return "700";
            }
            else if (id == "515")
            {
                return "701";
            }
            else if (id == "520")
            {
                return "702";
            }
            else if (id == "583")
            {
                return "703";
            }
            else if (id == "200")
            {
                return "800";
            }
            else if (id == "999")
            {
                return "999";
            }
            else
            {
                int site = Int32.Parse(id) + 227;
                while (site >= 700 && site <= 703 || site == 800)
                {
                    site++;
                }
                return site.ToString();
            }
        }

        public XVistaConnection(DataSource source)
            : base(source)
        {
            _mockSiteId = getMockSiteId(source.SiteId.Id);

            //setXmlSource(mockSiteId);

            this.saveResults = false;
            this.saveAuthConnect = false;
            this.displayRPCList = false;

            requests = new List<string>();
            responses = new List<string>();

            // Taken from VistaDaoFactory.getConnection
            if (this.ConnectStrategy == null)
            {
                //this.ConnectStrategy = new VistaDirectConnectStrategy(this);
                this.ConnectStrategy = new VistaNatConnectStrategy(this);
            }

            _sqliteDao = new SqliteDao();
        }

        public override object query(MdoQuery mq, AbstractPermission permission = null)
        {
            string request = mq.buildMessage();

            string response = (string)base.query(mq, permission);

            if (displayRPCList)
            {
                Console.WriteLine(request);
            }

            if (saveAuthConnect || (Account.IsAuthorized && !isCreateContextRequest(request) && !isDisconnectRequest(request)))
            {
                requests.Add(request);
                responses.Add(response);

                if (saveResults)
                {
                    // lazily create tables if they don't exist
                    if (!_sqliteDao.hasTable(_mockSiteId))
                    {
                        _sqliteDao.createTableForSite(_mockSiteId);
                    }

                    // see if this query has already been saved
                    object dbObj = _sqliteDao.getObject(_mockSiteId, gov.va.medora.utils.StringUtils.getMD5Hash(request));
                    if (dbObj == null)
                    {
                        _sqliteDao.saveObject(_mockSiteId, request, response);
                    }
                    else if (updateResults)
                    {
                        _sqliteDao.updateObject(_mockSiteId, gov.va.medora.utils.StringUtils.getMD5Hash(request), response);
                    }

                    //if (updateResults)
                    //{
                    //    _sqliteDao.saveOrUpdateObject(_mockSiteId, request, response);
                    //}
                    //else
                    //{
                    //    _sqliteDao.saveObject(_mockSiteId, request, response);
                    //}
                    //xmlSource.addRequest(mq, response, UpdateResults);
                }
            }

            return response;
        }

        public override object query(string request, AbstractPermission permission = null)
        {
            return (string)base.query(request, permission);
        }

        private Boolean isDisconnectRequest(string request)
        {
            return request.Contains("#BYE#");
        }


        private Boolean isCreateContextRequest(string request)
        {
            return request.Contains("CREATE CONTEXT");
        }
    }
}
