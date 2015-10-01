using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Collections.Specialized;
using System.Xml;
using System.IO;
using gov.va.medora.utils;
using System.Reflection;
using gov.va.medora.mdo.exceptions;
using gov.va.medora.mdo;
using System.Xml.XPath;
using gov.va.medora.mdo.dao.vista;
using System.Collections;

namespace gov.va.medora.utils.mock
{
    public class MockXmlSource
    {
        internal string siteId;
        XmlDocument siteDoc;

        //private bool verifyRpc = true;
        public bool VerifyRpc {get; set;}
        private bool updateRpc = false;

        private const string QUERY_NODE = "query";
        private const string REQUEST_NODE = "request";
        private const string RESPONSE_NODE = "response";
        private const string RPC_ATTR = "rpc";
        private const string TYPE_ATTR = "type";

        private string XmlResourcePath 
        {
            // make the replacement when running in mdo-x since we really want to save
            // to the mdo-test project.  This assumes a consistent project structure
            //   mdo.net
            //      mdo
            //      mdo-test
            //      mdo-x
            get 
            { 
                string temp = ResourceUtils.XmlResourcesPath.Replace("mdo-x", "mdo-test");
                temp = temp.Replace("mdws\\resources", "..\\mdo\\mdo-test\\resources");
                return temp;
            }
        }

        private string DataResourcePath
        {
            // see XmlResourcePath
            get 
            { 
                string temp = ResourceUtils.DataResourcesPath.Replace("mdo-x", "mdo-test");
                temp = temp.Replace("mdws\\resources", "..\\mdo\\mdo-test\\resources");
                return temp;
            }
        }

        private string MockConnectionFileName
        {
            get { return String.Format("MockConnection{0}.xml", siteId); }
        }

        private string MockConnectionFilePath
        {
            get { return Path.Combine(XmlResourcePath, MockConnectionFileName); }
        }

        public int ResponseCount
        {
            get { return (null != siteDoc) ? siteDoc.SelectNodes("//query").Count : 0; }
        }
        /// <summary>
        /// Constructs a MockXmlSource
        /// </summary>
        /// <param name="sideId">SiteId to retrieve mock data from</param>
        /// <param name="resourceDir">Path to resources directory</param>
        public MockXmlSource(string siteId)
        {
            this.siteId = siteId;

            if (String.IsNullOrEmpty(this.siteId))
            {
                throw new ArgumentNullException("siteId", "siteId must not be null or empty");
            }

            string siteFilePath = MockConnectionFilePath;

            if (!File.Exists(siteFilePath))
            {
                StreamWriter f = File.CreateText(siteFilePath);
                f.WriteLine("<queries>");
                f.WriteLine("</queries>");
                f.Flush();
                f.Close();
                Console.WriteLine("The file: " + siteFilePath + " does not exist and has been created.");
            }


            loadXml(siteFilePath);
        }

        public MockXmlSource(string siteId, bool updateRpc)
            : this(siteId)
        {
            updateRpc = true;
        }

        private void loadXml(string path)
        {
            try
            {
                siteDoc = new XmlDocument();
                siteDoc.Load(path);
            }
            catch (Exception ex)
            {
                throw new IOException("Unable to load XML file: " + MockConnectionFileName, ex);
            }
        }

        /// <summary>
        /// Generate the value to store in the paramenter attribute
        /// if the param type is a list, a hashcode will be generated from that list
        /// </summary>
        /// <param name="param"></param>
        /// <returns></returns>
        private string getParamValue(VistaQuery.Parameter param)
        {
            string value = "";
            if (param.Type == 3)
            {
                foreach (String key in param.List.AllKeys)
                {
                    object temp = param.List[key];
                    if (null == temp)
                    {
                        temp = "null";
                    }

                    value += (key + temp.GetHashCode().ToString());
                }

                value = value.GetHashCode().ToString();
            }
            else if (param.Type == 4)
            {
                value = param.Text;
            }
            else
            {
                value = param.Encrypted ? param.Original : param.Value;
            }
            return value;
        }

        /// <summary>
        /// Load the parameters which identify the request
        /// </summary>
        /// <param name="mq"></param>
        /// <param name="xpath"></param>
        /// <param name="request"></param>
        private void getRequestIdentifiers(MdoQuery mq, ref string xpath, ref string request)
        {
            // generate the rpc string sent to VistA
            request = mq.buildMessage();
            request = StringUtils.stripInvalidXmlCharacters(request);
            request = StringUtils.stripNonPrintableChars(request);

            // build the xPath of the query node in the mockconnection file keyed
            // from rpcname, num rpc params, and each rpc param value
            string additionalArgs = "";
            int id = 1;
            foreach (VistaQuery.Parameter param in mq.Parameters)
            {
                string value = getParamValue(param);
                additionalArgs += String.Format(" and @p{0}='{1}'", id++, value);
            }

            xpath = String.Format("//{0}[@{1}='{2}' and count(@*)={3} {4}]", QUERY_NODE, RPC_ATTR, mq.RpcName, mq.Parameters.Count + 1, additionalArgs);
        }


        /// <summary>
        /// Looks up the query with the xpath
        /// </summary>
        /// <param name="xpath"></param>
        /// <returns></returns>
        private XmlNode selectRpc(string xpath)
        {
            XmlNodeList nodes = siteDoc.SelectNodes(xpath);
            if (nodes.Count > 1)
            {
                throw new DataException("Multiple Queries found using xPath: " + xpath);
            }

            if (null == nodes || nodes.Count == 0)
            {
                return null;
            }

            return nodes[0];
        }


        /// <summary>
        /// Validates the saved request string matches the vq.buildMessage() string
        /// </summary>
        /// <param name="query">a valid query node</param>
        /// <param name="request">the request to compare to the stored request</param>
        private void verifyStoredRequest(XmlNode query, string request)
        {
            string rpcName = query.Attributes.GetNamedItem(RPC_ATTR).Value;

            XmlNode value = query.SelectSingleNode(REQUEST_NODE);
            if (null != value)
            {
                if (VerifyRpc)
                {
                    if (!value.InnerText.Equals(request))
                    {
                        if (updateRpc)
                        {
                            updateRequestRpc(query, request);
                            saveSource();
                        }
                        else
                            throw new DataMisalignedException(String.Format("The stored value for rpc: {0} does not match the value from MdoQuery.buildMessage", rpcName));
                    }
                }
            }
            else
            {
                if (updateRpc)
                {
                    updateRequestRpc(query, request);
                    saveSource();
                }
                else
                    throw new DataException(String.Format("value for rpc: {0} is missing from the mock data", rpcName));
            }
        }

        /// <summary>
        /// Get the response from the selected query node
        /// </summary>
        /// <param name="query">a valid query node</param>
        /// <returns>the response string</returns>
        private string getResponse(XmlNode query)
        {
            string rpcName = query.Attributes.GetNamedItem(RPC_ATTR).Value;

            XmlNode result = query.SelectSingleNode(RESPONSE_NODE);
            if (null != result)
            {
                if (null == result.Attributes.GetNamedItem("type", ""))
                {
                    return result.InnerText;
                }
                else
                {
                    return FileIOUtils.readFromFile(Path.Combine(DataResourcePath, result.InnerText));
                }
            }
            
            throw new DataException(String.Format("response for rpc: {0} is missing from the mock data", rpcName));            
        }

        /// <summary>
        /// Performs the mock query, retrieving the result from the preloaded XML document
        /// but throws an exception if the document does not contain the call
        /// </summary>
        /// <param name="request">the request to look up</param>
        /// <returns>a valid response string</returns>
        public string query(MdoQuery mq)
        {
            string request = "";
            string xPathQueryBase = "";

            getRequestIdentifiers(mq, ref xPathQueryBase, ref request);

            XmlNode query = selectRpc(xPathQueryBase);

            if (null != query)
            {
                verifyStoredRequest(query, request);

                return getResponse(query);
            }
            else
            {
                throw new ArgumentException("No such call in " + MockConnectionFileName + ":\n@ xpath: " + xPathQueryBase);
            }
        }

        /// <summary>
        /// Adds the request/response to the document or saves the response to an external file
        /// </summary>
        /// <param name="request">request to save</param>
        /// <param name="response">the response for the request</param>
        public void addRequest(MdoQuery mq, string response, bool update)
        {
            string request = "";
            string xPathQueryBase = "";

            getRequestIdentifiers(mq, ref xPathQueryBase, ref request);

            XmlNode query = selectRpc(xPathQueryBase);
            if (null == query)
            {
                addQuery(mq.RpcName, mq.Parameters, request, response);
            }
            else if (update)
            {
                updateQuery(query, request, response);
            }
        }

        /// <summary>
        /// Checks the response to see if it is multi line
        /// </summary>
        /// <param name="response"></param>
        /// <returns>true if the response contains multiple lines</returns>
        private bool isFileWorthy(string response) 
        {
            String[] lines = StringUtils.split(response, StringUtils.CRLF);
            lines = StringUtils.trimArray(lines);

            return lines.Length > 1;
        }

        /// <summary>
        /// Adds or updates an existing query node in the mock connection file
        /// </summary>
        /// <param name="node"></param>
        /// <param name="request"></param>
        /// <param name="response"></param>
        private void addUpdateResponseNode(XmlNode node, string request, string response)
        {
            if (isFileWorthy(response))
            {
                // save response to dat file
                string dataFileName = "";
                
                XmlAttribute type = node.Attributes["type"];
                if (null == type)
                {
                    type = siteDoc.CreateAttribute("type");
                    type.Value = "file";
                    node.Attributes.Append(type);

                    dataFileName = (siteId + request).GetHashCode().ToString() + ".dat";
                }
                else
                {
                    dataFileName = node.InnerText;
                }

                node.InnerText = dataFileName;

                FileIOUtils.writeToFile(
                    Path.Combine(DataResourcePath, dataFileName),
                    response);
            }
            else
            {
                node.RemoveAll();
                // save response directly to the xml file
                XmlCDataSection cdata = siteDoc.CreateCDataSection(response);
                node.AppendChild(cdata);
                node.Attributes.RemoveAll();
            }
        }

        /// <summary>
        /// Adds a query to the mock connection file
        /// </summary>
        /// <param name="rpcName"></param>
        /// <param name="rpcParams"></param>
        /// <param name="request"></param>
        /// <param name="response"></param>
        private void addQuery(string rpcName, ArrayList rpcParams, string request, string response)
        {
            XmlNode newQuery = siteDoc.CreateNode(XmlNodeType.Element, QUERY_NODE, null);

            XmlAttribute nameAttr = siteDoc.CreateAttribute(RPC_ATTR);
            nameAttr.Value = rpcName;
            newQuery.Attributes.Append(nameAttr);

            int id = 1;
            foreach (VistaQuery.Parameter param in rpcParams)
            {
                XmlAttribute attr = siteDoc.CreateAttribute("p" + (id++));
                attr.Value = getParamValue(param);
                newQuery.Attributes.Append(attr);
            }
            
            XmlNode value = siteDoc.CreateNode(XmlNodeType.Element, REQUEST_NODE, null);
            value.InnerText = request;
            newQuery.AppendChild(value);

            XmlNode res = siteDoc.CreateNode(XmlNodeType.Element, RESPONSE_NODE, null);
            newQuery.AppendChild(res);

            addUpdateResponseNode(res, request, response);

            XmlNode queries = siteDoc.DocumentElement;
            queries.AppendChild(newQuery);

            saveSource();
        }

        private void updateRequestRpc(XmlNode query, string request)
        {
            XmlNode val = query.SelectSingleNode(REQUEST_NODE);
            val.InnerText = request;
        }

        private void updateResponse(XmlNode query, string request, string response)
        {
            XmlNode val = query.SelectSingleNode(RESPONSE_NODE);
            addUpdateResponseNode(val, request, response);
        }

        /// <summary>
        /// Updates a query in the mock connection file
        /// </summary>
        /// <param name="query"></param>
        /// <param name="request"></param>
        /// <param name="response"></param>
        private void updateQuery(XmlNode query, string request, string response)
        {
            updateRequestRpc(query, request);
            updateResponse(query, request, response);

            saveSource();
        }

        private void saveSource() {
            siteDoc.Save(MockConnectionFilePath);
        }

        public void query2(MdoQuery mq)
        {
            string request = "";
            string xpath = "";
            getRequestIdentifiers(mq, ref xpath, ref request);

            XmlDocument doc = new XmlDocument();
            doc.Load( Path.Combine(XmlResourcePath, "MockConnection" + siteId + ".xml"));

            XmlNodeList list = doc.SelectNodes("//query");
            foreach (XmlNode node in list)
            {
                string value = node.SelectSingleNode("value").InnerText;
                XmlNode responseNode = node.SelectSingleNode("response");

                if (value.Equals(request))
                {
                    String response = responseNode.InnerText;
                    if (null != responseNode.Attributes["type"])
                    {
                        response = FileIOUtils.readFromFile(Path.Combine(DataResourcePath, response));
                    }

                    addRequest(mq, response, false);
                    return;
                }
            }
        }
    }
}
